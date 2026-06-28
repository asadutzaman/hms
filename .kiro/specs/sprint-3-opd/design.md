# Sprint 3 — OPD Design

**Companion to**: `requirements.md`
**Pattern reference**: `backend/app/Http/Controllers/AppointmentController.php` and `frontend/src/app/modules/appointment/`

---

## 1. Architecture Decisions

### 1.1 Multi-table split (continues Sprint 2 pattern)

8 tables, not a monolith — chosen so each entity can be queried/updated independently:

| Table | Purpose | Lives here because |
|---|---|---|
| `opd_visits` | Encounter header (patient, doctor, dept, status, opd_no) | Most-queried, needs tight indexes |
| `opd_vitals` | One row per visit (vitals captured once) | 1:1 with visit, queried with visit |
| `opd_diagnoses` | 1..n diagnoses per visit | Multi-row, queried independently for ICD stats |
| `opd_prescriptions` | Rx header (per visit) | 1:1 with visit, prints separately |
| `opd_prescription_items` | Rx line items | 1:n, queried separately for pharmacy later |
| `opd_investigation_orders` | Order header (could be 0..n per visit for grouped panels) | Different lifecycle than Rx |
| `opd_investigation_order_items` | Individual tests per order | Future LIS integration |
| `opd_bills` | Bill header | 1:1 with visit |
| `opd_bill_items` | Bill line items | 1:n |
| `opd_bill_payments` | Payment records | 1:n (partial payments allowed) |
| `opd_visit_audit_logs` | Status transitions + edits | High write volume, separated |
| `lab_tests` | Catalogue (seeded) | Reused across OPD + future IPD + LIS |

### 1.2 Status as enum, transitions in repo

Status is `enum('waiting','vitals_taken','in_consultation','completed','billed','closed','cancelled')` default `waiting`. Allowed transitions are enforced in `OpdVisitRepository::transitionStatus()` which wraps the audit log write in the same transaction. Validators only allow the next legal state.

### 1.3 OPD No generation — reuse `code_sequences`

`code_sequences` already exists (see `2025_10_21_165958_create_code_sequences_table.php`) and Sprint 1/2 added labels `patient` and `appointment` via enum migrations. A new migration `2026_07_xx_add_opd_to_code_sequences_label_enum` extends the enum with `opd_visit`. The repository calls `CodeSequenceService::next('opd_visit', today)` which returns `OPD-YYYYMMDD-####`.

### 1.4 One-active-visit-per-doctor guarantee (DB-level)

A **partial unique index** enforces "only one `in_consultation` visit per doctor":

```sql
CREATE UNIQUE INDEX idx_opd_visits_one_active_per_doctor
  ON opd_visits (doctor_id)
  WHERE status = 'in_consultation';
```

Postgres-friendly. On MySQL the same guarantee is enforced in the repository via `SELECT ... FOR UPDATE` inside a transaction (MySQL does not support partial indexes). Repository throws `OpdConflictException` → mapped to 409 response.

### 1.5 Billing snapshot vs live pricing

Bill items store `unit_price`, `quantity`, `line_total`, `description_snapshot` at the time of bill creation. Future price changes in `lab_tests` or drug catalogue do NOT retroactively modify old bills. This is a deliberate decision for audit/integrity.

### 1.6 Print = browser print with @media print

No PDF library. Three routes return server-rendered Blade views (`resources/views/opd/print/{ticket,prescription,bill}.blade.php`) with print-optimised CSS. The "Print" button on the React UI calls `window.open(route, '_blank')` which triggers the browser's native print dialog. This matches the project's existing zero-new-dependencies posture.

### 1.7 Foreign keys to `employees` — same deferred pattern as Sprint 2

`opd_visits.doctor_id`, `created_by`, `updated_by`, `closed_by`, `billed_by` are `unsignedBigInteger` (no FK) in the base migration. A follow-up migration `add_opd_employee_fks_when_employees_exists.php` (idempotent, same shape as Sprint 2's `add_appointment_doctor_fks_when_employees_exists.php`) attaches FKs once `employees` exists.

---

## 2. Database

### 2.1 Tables

```sql
-- opd_visits
id, opd_no (unique), patient_id FK, appointment_id FK nullable,
doctor_id, department_id, visit_type enum('appointment','walk_in'),
visit_date date, status enum default 'waiting',
chief_complaint text, history text, examination text, clinical_notes text,
consultation_start_at timestamp, consultation_end_at timestamp,
created_by, updated_by, closed_by, closed_at, created_at, updated_at, deleted_at
INDEX (visit_date), (doctor_id, visit_date), (patient_id, visit_date DESC),
      (status, visit_date), UNIQUE (opd_no)

-- opd_vitals
id, opd_visit_id FK unique, systolic, diastolic, pulse, temperature, spo2,
weight, height, bmi (generated or computed in app), recorded_by, recorded_at

-- opd_diagnoses
id, opd_visit_id FK, icd10_code, description, diagnosis_type enum('primary','secondary'),
sequence int, created_at
INDEX (opd_visit_id), (icd10_code)

-- opd_prescriptions
id, opd_visit_id FK unique, prescribed_by, prescribed_at, notes text, created_at

-- opd_prescription_items
id, opd_prescription_id FK, drug_name, dose, frequency enum, duration_days,
route enum, instructions text, sequence int
INDEX (opd_prescription_id)

-- opd_investigation_orders
id, opd_visit_id FK, ordered_by, ordered_at, status enum default 'ordered', notes

-- opd_investigation_order_items
id, order_id FK, lab_test_id FK, test_name_snapshot, price_snapshot, sequence

-- opd_bills
id, opd_visit_id FK unique, bill_no unique, subtotal, discount, tax, total, paid,
balance, status enum('unpaid','partial','paid','refunded','waived') default 'unpaid',
billed_by, billed_at, created_at
INDEX (status, billed_at)

-- opd_bill_items
id, opd_bill_id FK, item_type enum('consultation','prescription','investigation','other'),
description, quantity, unit_price, line_total, source_id, source_type

-- opd_bill_payments
id, opd_bill_id FK, amount, payment_method enum('cash','card','insurance','mobile','other'),
reference_no, paid_by, paid_at

-- opd_visit_audit_logs
id, opd_visit_id FK, action enum('create','status_change','update','cancel','close'),
from_status, to_status, actor_id, payload jsonb, created_at
INDEX (opd_visit_id, created_at DESC)

-- lab_tests (catalogue)
id, code unique, name, default_price decimal, sample_type, tat_hours int,
is_active bool, created_at, updated_at
```

### 2.2 Index highlights

```sql
CREATE INDEX idx_opd_visits_doctor_date  ON opd_visits (doctor_id, visit_date);
CREATE INDEX idx_opd_visits_patient_date  ON opd_visits (patient_id, visit_date DESC);
CREATE INDEX idx_opd_visits_status_date   ON opd_visits (status, visit_date);
CREATE UNIQUE INDEX idx_opd_visits_opd_no ON opd_visits (opd_no);
CREATE INDEX idx_opd_dx_icd               ON opd_diagnoses (icd10_code);
CREATE INDEX idx_opd_bills_status         ON opd_bills (status, billed_at);
```

### 2.3 Migrations (in order)

```
2026_07_01_100001_create_lab_tests_table
2026_07_01_100002_create_opd_visits_table
2026_07_01_100003_create_opd_vitals_table
2026_07_01_100004_create_opd_diagnoses_table
2026_07_01_100005_create_opd_prescriptions_table
2026_07_01_100006_create_opd_prescription_items_table
2026_07_01_100007_create_opd_investigation_orders_table
2026_07_01_100008_create_opd_investigation_order_items_table
2026_07_01_100009_create_opd_bills_table
2026_07_01_100010_create_opd_bill_items_table
2026_07_01_100011_create_opd_bill_payments_table
2026_07_01_100012_create_opd_visit_audit_logs_table
2026_07_01_100013_add_opd_to_code_sequences_label_enum
2026_07_01_100014_seed_lab_tests_catalogue
2026_07_01_100015_add_opd_employee_fks_when_employees_exists  (idempotent)
```

---

## 3. API Endpoints

Base prefix: `/api/auth`. All endpoints follow the existing `TraitRest*` pattern.

### 3.1 OPD Visits — `/opd-visit`

| Method | Path | Method | Notes |
|---|---|---|---|
| GET | `/opd-visit` | `index` | list + filters (date range, doctor, status, patient) |
| GET | `/opd-visit/dropdown` | `dropdown` | id + opd_no + patient_name |
| GET | `/opd-visit/today` | `today` | today's visits, default sort token asc |
| GET | `/opd-visit/{id}` | `show` | full nested payload |
| POST | `/opd-visit` | `create` | from appointment or walk-in |
| PUT | `/opd-visit/{id}` | `update` | clinical fields, vitals (PUT per TraitRestUpdate) |
| PATCH | `/opd-visit/{id}` | `updatePartial` | transition status + small edits |
| DELETE | `/opd-visit/{id}` | `destroy` | soft delete only if `cancelled` |
| POST | `/opd-visit/bulk-destroy` | `destroyBulk` | |
| POST | `/opd-visit/{id}/transition` | `transition` | body `{to_status, payload?}` |
| POST | `/opd-visit/{id}/cancel` | `cancel` | body `{reason}` |

### 3.2 Vitals — `/opd-vital`

| GET `/opd-vital`, GET `/opd-vital/{id}`, POST, PUT, PATCH, DELETE, `/dropdown`, `/bulk-destroy` — standard `TraitRest*` set.

### 3.3 Diagnosis — `/opd-diagnosis`

Standard CRUD + `/by-visit/{visit_id}` collection endpoint + bulk-destroy.

### 3.4 Prescription — `/opd-prescription` + `/opd-prescription-item`

Standard CRUD on both. Items created via nested payload in the Rx create/update.

### 3.5 Investigation — `/opd-investigation-order` + `/opd-investigation-order-item`

Standard CRUD on both.

### 3.6 Bill — `/opd-bill` + `/opd-bill-item` + `/opd-bill-payment`

| Method | Path | Notes |
|---|---|---|
| GET | `/opd-bill/by-visit/{visit_id}` | returns bill + items + payments |
| POST | `/opd-bill/generate/{visit_id}` | creates bill from completed visit (idempotent) |
| POST | `/opd-bill/{id}/payment` | records a payment, updates status |
| POST | `/opd-bill/{id}/waive` | admin-only, sets status `waived` |

### 3.7 Lab catalogue — `/lab-test`

Standard CRUD + `/dropdown` + bulk-destroy + `/active` (list active only).

### 3.8 Reports — `/opd-report/*`

| Method | Path | Notes |
|---|---|---|
| GET | `/opd-report/dashboard/today` | counts by status, revenue today |
| GET | `/opd-report/doctor-wise` | `?from=&to=&doctor_id?` |
| GET | `/opd-report/pending-bills` | bill_status in (unpaid,partial) |
| GET | `/opd-report/revenue` | daily/weekly/monthly aggregate |

### 3.9 Print (Blade) — server-rendered, NOT under `/api/auth`

These are public within the authed web group:

| Method | Path | View |
|---|---|---|
| GET | `/opd/print/visit/{id}/ticket` | `opd/print/ticket.blade.php` |
| GET | `/opd/print/visit/{id}/prescription` | `opd/print/prescription.blade.php` |
| GET | `/opd/print/visit/{id}/bill` | `opd/print/bill.blade.php` |

Registered under `routes/web.php` inside the existing `auth` middleware group, NOT under the `/api/auth` prefix, because they return HTML not JSON.

---

## 4. Frontend

### 4.1 Module folder

```
frontend/src/app/modules/opd/
├── OPDRoutes.tsx
└── components/
    ├── OpdVisit/
    │   ├── OpdVisitList.tsx
    │   ├── OpdVisitForm.controller.tsx
    │   ├── OpdVisitForm.tsx
    │   ├── OpdVisitView.tsx
    │   └── partials/
    │       ├── VitalsSection.tsx
    │       ├── DiagnosisSection.tsx
    │       ├── PrescriptionSection.tsx
    │       ├── InvestigationSection.tsx
    │       └── BillingSection.tsx
    ├── OpdQueue/
    │   ├── OpdQueueBoard.tsx     (doctor's live queue)
    │   └── OpdQueueList.tsx
    ├── OpdReport/
    │   ├── OpdDashboard.tsx
    │   ├── DoctorWiseReport.tsx
    │   └── PendingBillsReport.tsx
    └── common/
        ├── PrintButton.tsx        (opens print URL in new tab)
        └── OPDStatusBadge.tsx
```

### 4.2 Pages & routes

| Path | Component | Role |
|---|---|---|
| `/opd/visits` | `OpdVisitList` | receptionist, nurse |
| `/opd/visits/new` | `OpdVisitForm` (create) | receptionist |
| `/opd/visits/:id/edit` | `OpdVisitForm` (edit) | doctor |
| `/opd/visits/:id` | `OpdVisitView` | all |
| `/opd/queue` | `OpdQueueBoard` | doctor, nurse |
| `/opd/lab-tests` | `LabTestList` (admin) | admin |
| `/opd/reports/dashboard` | `OpdDashboard` | admin, doctor |
| `/opd/reports/doctor-wise` | `DoctorWiseReport` | admin |
| `/opd/reports/pending-bills` | `PendingBillsReport` | cashier, admin |

### 4.3 UI patterns reused (no new libraries)

- **List page** — copy `appointment/components/Appointment/List/AppointmentList.tsx`; reuse `KTCard`, `KTDatatable`, `Metronic table` patterns.
- **Form controller** — copy `AppointmentForm.controller.tsx`; reuse the shared `<FormSection>`, `<Input>`, `<Select>` components under `frontend/src/app/components/forms/`.
- **View page** — copy `Appointment/View/AppointmentView.tsx` and add the 5 partials (Vitals, Diagnosis, Rx, Investigation, Billing) as tabs.
- **Print button** — `window.open(apiUrl + '/print/visit/' + id + '/ticket', '_blank')` with proper auth header injected via a server proxy route (`backend/routes/web.php` already has `auth` middleware that handles cookies/tokens — calls in new tab re-use the cookie session).
- **Status badge** — extend `frontend/src/app/components/Badges/StatusBadge.tsx` with the new OPD statuses (or copy and localise in `opd/common/OPDStatusBadge.tsx` to avoid touching shared component).
- **Dropdown selectors** — reuse `<Dropdown>` (`frontend/src/app/components/Dropdown/LiveSearch`).

### 4.4 API registration

`frontend/src/app/api/index.tsx` gets new sections `opdVisitAPI`, `opdVitalAPI`, `opdDiagnosisAPI`, `opdPrescriptionAPI`, `opdPrescriptionItemAPI`, `opdInvestigationOrderAPI`, `opdInvestigationOrderItemAPI`, `opdBillAPI`, `opdBillPaymentAPI`, `labTestAPI`, `opdReportAPI` — each a thin axios wrapper following the existing `appointmentAPI` shape.

---

## 5. Backend Skeleton Generation

Per `docs/AI_CONTEXT.md`, run:

```bash
php artisan imake:crud OpdVisit         OpdVisits         --all
php artisan imake:crud OpdVital         OpdVitals         --all
php artisan imake:crud OpdDiagnosis     OpdDiagnoses      --all
php artisan imake:crud OpdPrescription  OpdPrescriptions  --all
php artisan imake:crud OpdPrescriptionItem OpdPrescriptionItems --all
php artisan imake:crud OpdInvestigationOrder OpdInvestigationOrders --all
php artisan imake:crud OpdInvestigationOrderItem OpdInvestigationOrderItems --all
php artisan imake:crud OpdBill          OpdBills          --all
php artisan imake:crud OpdBillItem      OpdBillItems      --all
php artisan imake:crud OpdBillPayment   OpdBillPayments   --all
php artisan imake:crud LabTest          LabTests          --all
```

Then customise as documented in `tasks.md`. The `OpdVisit` controller additionally gets hand-written methods `transition`, `cancel`, `today`, `generateBill` (because they don't fit the generic CRUD traits).

---

## 6. Permissions

Create `backend/database/seeders/json/permission/hms/opdPermission.json` mirroring `appointmentPermission.json`. Scopes:

```
auth:opd:menuAccess    — index
auth:opd:list          — index, today, dropdown, queue
auth:opd:view          — show, getByWhere, auditLog, bill by-visit
auth:opd:create        — POST
auth:opd:update        — PUT, PATCH, transition, cancel
auth:opd:delete        — DELETE, bulk-destroy
auth:opd:bill          — generate-bill, record-payment, waive
auth:opd:print         — ticket, prescription, bill (HTML routes)
```

Seeded through the existing `PermissionSeeder` flow used by Patient + Appointment.

---

## 7. Files Affected (summary)

**Backend — new (≈ 70 files):**
- 11 migrations
- 11 models
- 11 controllers (+ `OpdReportController`)
- 11 repositories (+ `OpdReportRepository`)
- 11 resources
- 11 validators
- 1 permission json
- 3 print Blade views
- 1 service (`OpdVisitService` for status machine + bill generation)
- 1 seeder (`LabTestSeeder`)

**Backend — modified (≈ 5 files):**
- `routes/web.php` (add OPD routes group)
- `database/seeders/DatabaseSeeder.php` (call new seeders)
- `app/Services/CodeSequenceService.php` (no change needed; already generic)

**Frontend — new (≈ 25 files):**
- module folder per §4.1
- API entries in `frontend/src/app/api/index.tsx`
- routes registration in `frontend/src/app/routing/AppRoutes.tsx` (verify exact path from Sprint 2's `AppointmentRoutes.tsx`)

**Frontend — modified (≈ 3 files):**
- sidebar/menu config (verify exact file under `frontend/src/_metronic/layout/`)
- routing config (one new import + one new route group)
- API index (one new block)

---

## 8. Out-of-band

- **Smoke test**: `tests/Feature/OpdVisitTest.php` covering create-from-appointment, walk-in create, vitals save, transition matrix, bill generation, print HTML response status.
- **Manual verification** per `requirements.md` §5.
- **Documentation**: `docs/sessions/sprint-3-opd.md` post-completion.