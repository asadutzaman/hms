# Session: Sprint 2 — Appointment System (EPIC-02)

**Date**: 2026-06-26
**Branch**: `feature/appointment`
**Spec Location**: `.kiro/specs/sprint-2-appointment/`

---

## Context Read

- `docs/AI_CONTEXT.md` — project stack, module development process, rules
- `docs/AI_RULES.md` — coding rules, output format, architecture constraints
- `docs/HMS_Product_Backlog.xlsx` — sheets read: `📦 Product Backlog`, `🗓️ Sprint Plan`, `🏗️ Epic Summary`
- `docs/sessions/sprint-1-foundation.md` — Sprint 1 hand-off (Patient module pattern reference)

---

## Sprint 2 Features — EPIC-02 Appointment (76 Story Points)

| Feature ID | Feature Name | Priority | SP | Status |
|---|---|---|---|---|
| F-02-01 | Appointment Booking | Must Have | 13 | ✅ Complete |
| F-02-02 | Doctor Availability / Schedule Master | Must Have | 13 | ✅ Complete |
| F-02-03 | Appointment Status Lifecycle | Must Have | 8 | ✅ Complete |
| F-02-04 | Appointment Rescheduling | Must Have | 5 | ✅ Complete |
| F-02-05 | Walk-in Management | Must Have | 5 | ✅ Complete |
| F-02-06 | Queue Board / Live View | Must Have | 8 | ✅ Complete |
| F-02-07 | Waitlist Management | Should Have | 8 | ✅ Complete |
| F-02-08 | Appointment Reminders (SMS/Email) | Could Have | 8 | ⚠️ Backend stubs only |
| F-02-09 | Telemedicine Consultation | Won't Have | 8 | ⚠️ Data model ready, integration deferred |

---

## Architecture Decisions

### 1. Multi-table split (not single-table)

Split the appointment domain into 7 tables instead of one monolith:

| Table | Purpose | Why Split |
|---|---|---|
| `doctor_schedules` | Doctor availability templates | Reusable, versioned per doctor |
| `doctor_schedule_slots` | Weekly recurring time slots | One-to-many; queried independently for slot generation |
| `doctor_schedule_exceptions` | Blocked dates / overrides | Doesn't pollute slot queries |
| `appointments` | Booked visits | Most-queried; needs separate indexes |
| `appointment_slots` | Materialized slot instances | Pre-generated from schedules for fast availability lookups |
| `appointment_waitlists` | Patients waiting for cancellations | Different lifecycle than confirmed appointments |
| `appointment_audit_logs` | Status transitions + field changes | High write volume; doesn't slow down appointment queries |

**Rationale**: A single `appointments` table with embedded JSON would force full table scans for availability queries and complicate audit retention. Splitting tables matches query access patterns and enables targeted index strategies.

### 2. Materialized slot table (`appointment_slots`)

When a doctor schedule is created or appointments change, the system pre-generates `appointment_slots` rows for the next N days (default 14). The Queue and availableSlots API then does a single indexed lookup instead of running slot-generation logic on every request.

**Trade-off**: Extra storage vs. query latency. For a hospital with 30 doctors × 14 days × 30 slots/day = ~12,600 rows per 14-day window — trivial.

### 3. Composite + partial indexes

```sql
-- Most common query: "available slots for doctor X on date Y"
CREATE INDEX idx_appt_slots_lookup
  ON appointment_slots (doctor_id, slot_date, start_time)
  WHERE is_available = true;

-- Calendar view: "all appointments for doctor X on date Y"
CREATE INDEX idx_appts_doctor_date
  ON appointments (doctor_id, appointment_date)
  WHERE status IN ('scheduled','confirmed','checked_in','in_consultation');

-- Patient history
CREATE INDEX idx_appts_patient_date
  ON appointments (patient_id, appointment_date DESC);

-- Token uniqueness per doctor per day
CREATE UNIQUE INDEX idx_appts_token_per_day
  ON appointments (doctor_id, appointment_date, token_number);

-- Waitlist active queue
CREATE INDEX idx_waitlist_active
  ON appointment_waitlists (doctor_id, status, priority DESC, created_at)
  WHERE status = 'active';

-- Audit log lookups
CREATE INDEX idx_audit_appt
  ON appointment_audit_logs (appointment_id, created_at DESC);
```

**Rationale**: Partial indexes (`WHERE ...`) skip rows that won't match common filters, shrinking index size and speeding up the targeted queries that actually matter. Composite indexes ordered by selectivity (doctor → date → time) avoid the need for separate single-column indexes.

### 4. Foreign key strategy — deferred to employees table

Migration 100007 (`alter_patients_appointment_fks`):
- `appointments.patient_id` → `patients.id` (FK ON DELETE RESTRICT)

Migration 100009 (`add_appointment_doctor_fks_when_employees_exists`):
- Conditional FK creation. Wrapped in `if (Schema::hasTable('employees'))` check.
- When `employees` table is built (Sprint 4 — Employee Master F-13-01), this migration adds:
  - `appointments.doctor_id` → `employees.id`
  - `appointments.created_by` → `employees.id`
  - `appointments.updated_by` → `employees.id`
  - `appointments.cancelled_by` → `employees.id`
  - `doctor_schedules.doctor_id` → `employees.id`
  - `appointment_waitlists.doctor_id` → `employees.id`

**Rationale**: Employees table is being built in Sprint 4. Defining FKs now would block migrations on a dependent table. The idempotent migration 100009 runs harmlessly on every `migrate` and activates the FKs as soon as the dependency exists. This lets Sprint 2 ship independent of Sprint 4.

Until then, the `doctor_id` columns are `unsignedBigInteger` with no FK — the application layer is responsible for referential integrity.

### 5. Audit trail via database triggers

`appointment_audit_logs` are written from a database trigger on `appointments` (UPDATE OF status, appointment_date, doctor_id, start_time) plus application-level writes for create/cancel operations. The trigger captures before/after values as JSONB for full forensic reconstruction.

```sql
CREATE OR REPLACE FUNCTION fn_appointment_audit() RETURNS TRIGGER AS $$
BEGIN
  IF (TG_OP = 'UPDATE') THEN
    IF OLD.status IS DISTINCT FROM NEW.status THEN
      INSERT INTO appointment_audit_logs (...) VALUES (..., 'status_change', ...);
    END IF;
    -- additional field comparisons...
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### 6. Token number generation

Token numbers are per-doctor-per-day (1, 2, 3…). Generated atomically using a Postgres sequence (`appointment_token_seq`) or application-side advisory lock to prevent gaps/duplicates when concurrent bookings race. Display: "Dr. Smith — Token #14".

### 7. Soft deletes

`appointments`, `doctor_schedules`, `appointment_waitlists` all use Laravel soft deletes (`deleted_at`). Hard deletes are reserved for GDPR / data retention purges.

---

## Backend API Inventory (27 Routes)

### Appointments (`/appointment`)

| Method | Path | Controller Method | Purpose |
|---|---|---|---|
| GET | `/` | `index` | List with filters & pagination |
| GET | `/{id}` | `show` | Single appointment |
| POST | `/` | `store` | Create appointment |
| PUT/PATCH | `/{id}` | `update` | Full / partial update |
| DELETE | `/{id}` | `destroy` | Soft delete |
| POST | `/bulk-delete` | `bulkDelete` | Multi-select delete |
| GET | `/dropdown` | `dropdown` | Lightweight dropdown data |
| GET | `/available-slots` | `availableSlots` | Get free slots for doctor+date |
| POST | `/{id}/cancel` | `cancel` | Cancel appointment |
| POST | `/{id}/confirm` | `confirm` | Confirm scheduled appointment |
| POST | `/{id}/check-in` | `checkIn` | Mark patient arrived |
| POST | `/walk-in` | `walkIn` | Walk-in registration |
| POST | `/{id}/start-consultation` | `startConsultation` | Doctor begins visit |
| POST | `/{id}/complete` | `complete` | Mark consultation complete |
| POST | `/{id}/mark-no-show` | `markNoShow` | Patient didn't show |
| POST | `/{id}/reschedule` | `reschedule` | Move to new slot |
| GET | `/queue` | `queue` | Live queue board data |
| GET | `/dashboard` | `dashboard` | Stats for the day |
| GET | `/{id}/audit-log` | `auditLog` | Status history |

### Doctor Schedules (`/appointment/doctor-schedules`)

| Method | Path | Purpose |
|---|---|---|
| GET | `/` | List all schedules |
| GET | `/{id}` | Single schedule |
| POST | `/` | Create schedule |
| PUT/PATCH | `/{id}` | Update schedule |
| DELETE | `/{id}` | Soft delete |
| POST | `/bulk-delete` | Multi-delete |
| GET | `/by-doctor/{doctorId}` | All schedules for a doctor |
| GET | `/active` | Currently active schedules |
| GET | `/{id}/slots` | Get weekly slots |
| POST | `/{id}/slots` | Add slot |
| PUT | `/{id}/slots/{slotId}` | Update slot |
| DELETE | `/{id}/slots/{slotId}` | Remove slot |
| GET | `/{id}/exceptions` | List blocked dates |
| POST | `/{id}/exceptions` | Add exception |
| DELETE | `/{id}/exceptions/{exceptionId}` | Remove exception |
| POST | `/{id}/clone` | Clone schedule |
| POST | `/{id}/set-default` | Mark as default |

### Waitlists (`/appointment/waitlists`)

| Method | Path | Purpose |
|---|---|---|
| GET/POST/PUT/DELETE | standard CRUD | Standard CRUD |
| GET | `/active` | Active entries |
| POST | `/{id}/notify` | Notify patient of slot |
| POST | `/notify-all` | Broadcast to active waitlist |
| POST | `/{id}/convert` | Convert to appointment |
| POST | `/{id}/cancel` | Cancel waitlist entry |
| POST | `/{id}/expire` | Mark expired |
| POST | `/reorder` | Reorder priority |

---

## Business Rules Implemented

### Status state machine

```
scheduled ─┬─► confirmed ─┬─► checked_in ─► in_consultation ─► completed
           │              │                                │
           │              └─► no_show ────────► closed
           │              
           ├─► cancelled ─────────────────────► closed
           └─► rescheduled ───────────────────► scheduled (new record)
```

Transitions enforced in `AppointmentController` via guard clauses. Invalid transitions return 422.

### Conflict prevention

`AppointmentRepository::checkConflict()` runs inside the booking transaction:
- Same `doctor_id` + same `appointment_date` + overlapping `[start_time, end_time]` → reject
- Same `patient_id` + same `appointment_date` + overlapping window → warn (allow override flag)

### Slot generation

When a `DoctorSchedule` is created, `AppointmentSlotGenerator` (queued job) materializes `appointment_slots` for the next 14 days from the weekly template. Re-runs on slot add/edit/delete. On appointment booking, the matching `appointment_slot` row flips `is_available = false`.

### Auto token assignment

Token numbers are issued in insertion order within a transaction using `SELECT pg_advisory_xact_lock(hashtext('appointment_token:' || doctor_id || ':' || date))`. Guarantees uniqueness without gaps under concurrent bookings.

### Audit log triggers

Database-level trigger fires on appointment UPDATE of `status`, `appointment_date`, `doctor_id`, `start_time`. Application-level writes on create and cancel. Each log captures:
- `appointment_id`
- `action` (`status_change`, `rescheduled`, `cancelled`, `created`, `no_show`)
- `old_values` / `new_values` (JSONB)
- `actor_id`, `actor_type`
- `reason` (optional)
- `created_at`

---

## Frontend File Inventory (created this sprint)

```
frontend/src/app/api/
  Appointment/Appointment.api.ts
  DoctorSchedule/DoctorSchedule.api.ts
  AppointmentWaitlist/AppointmentWaitlist.api.ts
  index.tsx                                  [modified]

frontend/src/app/routing/
  AdminRoutes.tsx                            [modified]

frontend/src/_metronic/layout/components/sidebar/sidebar-menu/
  LeftSidebar.menu.tsx                       [modified — added Appointment group]

frontend/src/app/modules/appointment/
  AppointmentRoutes.tsx
  components/
    Appointment/
      List/AppointmentList.controller.tsx
      List/AppointmentList.filter.tsx
      List/AppointmentList.listing.tsx
      List/AppointmentList.pagination.tsx
      Form/AppointmentForm.controller.tsx
      Form/AppointmentForm.form.tsx
      View/AppointmentView.controller.tsx
      View/AppointmentView.view.tsx
      Actions/Appointment.actions.ts
    DoctorSchedule/
      List/DoctorScheduleList.controller.tsx
      List/DoctorScheduleList.filter.tsx
      List/DoctorScheduleList.listing.tsx
      List/DoctorScheduleList.pagination.tsx
      Form/DoctorScheduleForm.controller.tsx
      Form/DoctorScheduleForm.form.tsx
      View/DoctorScheduleView.controller.tsx
      View/DoctorScheduleView.view.tsx
      Actions/DoctorSchedule.actions.ts
    WalkIn/WalkIn.controller.tsx
    Queue/Queue.controller.tsx
```

---

## Backend File Inventory (created this sprint)

```
backend/database/migrations/
  100001_create_doctor_schedules_table.php
  100002_create_doctor_schedule_slots_table.php
  100003_create_doctor_schedule_exceptions_table.php
  100004_create_appointments_table.php
  100005_create_appointment_slots_table.php
  100006_create_appointment_waitlists_table.php
  100007_alter_patients_appointment_fks.php
  100008_create_appointment_audit_logs_table.php
  100009_add_appointment_doctor_fks_when_employees_exists.php
  100010_create_appointment_code_sequences_table.php

backend/app/Models/
  DoctorSchedule.php
  DoctorScheduleSlot.php
  DoctorScheduleException.php
  Appointment.php
  AppointmentSlot.php
  AppointmentWaitlist.php
  AppointmentAuditLog.php
  AppointmentCodeSequence.php

backend/app/Enums/
  AppointmentStatus.php
  AppointmentSource.php
  ConsultationMode.php
  ScheduleType.php
  WaitlistStatus.php

backend/app/Repositories/
  DoctorScheduleRepository.php
  DoctorScheduleSlotRepository.php
  AppointmentRepository.php
  AppointmentSlotRepository.php
  AppointmentWaitlistRepository.php
  AppointmentAuditLogRepository.php

backend/app/Validators/
  AppointmentValidator.php
  DoctorScheduleValidator.php
  AppointmentWaitlistValidator.php

backend/app/Http/Resources/
  DoctorScheduleResource.php
  DoctorScheduleSlotResource.php
  AppointmentResource.php
  AppointmentSlotResource.php
  AppointmentWaitlistResource.php
  AppointmentAuditLogResource.php

backend/app/Http/Controllers/Appointment/
  AppointmentController.php
  DoctorScheduleController.php
  AppointmentWaitlistController.php

backend/database/seeders/
  CodeSequenceSeeder.php                    [modified — added APPT prefix]
```

---

## Permissions Map (for RBAC seeding)

```
auth:appointment:menuAccess
auth:appointment:create
auth:appointment:view
auth:appointment:edit
auth:appointment:delete
auth:appointment:multiSelect

auth:doctor-schedule:menuAccess
auth:doctor-schedule:create
auth:doctor-schedule:view
auth:doctor-schedule:edit
auth:doctor-schedule:delete
auth:doctor-schedule:multiSelect

auth:appointment-waitlist:menuAccess
auth:appointment-waitlist:create
auth:appointment-waitlist:view
auth:appointment-waitlist:edit
auth:appointment-waitlist:delete
```

---

## Verification Checklist

- [x] All 10 migrations applied successfully
- [x] All 8 models have proper relationships defined
- [x] All 6 repositories extend BaseRepository pattern
- [x] All 27 routes registered in `web.php` and `api.php`
- [x] All 6 resources implement `toArray()` correctly
- [x] All 3 validators return consistent error structure
- [x] All 5 enums have helper methods (`label()`, `color()`, `options()`)
- [x] Code sequences seeded with `APPT-` prefix
- [x] Frontend API services registered in `api/index.tsx`
- [x] Frontend routes lazy-loaded in `AdminRoutes.tsx`
- [x] Sidebar menu exposes Appointment Management group
- [x] Appointment list with 7 filters and $expand joins
- [x] Appointment form with Patient/Schedule/Notes tabs
- [x] Appointment view with sections + audit trail
- [x] Walk-in with patient picker (existing/new toggle)
- [x] Queue board with 3-column kanban + stats
- [x] Doctor schedule CRUD with slots editor

---

## Known Limitations / Future Work

### F-02-08 Appointment Reminders
- Backend has stub endpoints (`send_sms_reminder`, `send_email_reminder` flags on appointment)
- Needs: Laravel scheduler + queue worker for reminder dispatch
- Needs: SMS provider integration (Twilio / bulk sms bd)
- Needs: Email template engine

### F-02-09 Telemedicine
- `consultation_mode` enum supports `telemedicine`
- Appointment lifecycle handles `start_consultation` for any mode
- Needs: Video room provider integration (Twilio Video, Daily.co, Jitsi)
- Needs: Patient-side link generation
- Needs: Recording + consent flow

### Doctor FK enforcement
- Currently relies on application-layer integrity
- Migration 100009 will activate FKs when Sprint 4 ships the `employees` table
- Until then: orphan doctor_ids allowed (will be rejected in API responses with 422)

### Waitlist notification delivery
- `/notify` and `/notify-all` endpoints update `notified_at` timestamp
- Actual notification dispatch (SMS / email / push) is not yet wired

---

## Next Steps

1. Wire RBAC permissions for Appointment group into role seeder
2. Build Employee Master (Sprint 4) — activates the deferred FK migrations
3. Implement SMS reminder cron job (F-02-08)
4. Add video consultation integration (F-02-09)
5. Begin Sprint 3 — EPIC-03 (Clinical Records / EMR)
