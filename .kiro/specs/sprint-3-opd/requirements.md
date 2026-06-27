# Sprint 3 — OPD Management (EPIC-03)

**Status**: Draft — awaiting approval
**Depends on**: Sprint 1 (Patient), Sprint 2 (Appointment, DoctorSchedule)
**Reuses**: `patients`, `employees` (when available), `appointments`, `departments`, `doctor_schedules`, `application_settings`

---

## 1. Scope

This sprint delivers the **Out-Patient Department (OPD)** module — the clinical workflow that begins when a patient arrives for consultation and ends when they are discharged (with or without a prescription). It includes vitals capture, clinical notes, diagnosis (ICD-10), prescription, investigation requests, billing for OPD charges, and printable tickets / prescriptions / bills.

### In Scope (this sprint)

| # | Feature | Description |
|---|---|---|
| F-03-01 | OPD Visit Registration | Reception / nurse creates an OPD Visit from a confirmed Appointment OR as a walk-in. Generates OPD No. |
| F-03-02 | Vitals Capture | Nurse records BP, pulse, temperature, SpO₂, weight, height, BMI before consultation. |
| F-03-03 | Doctor Consultation | Doctor sees the queue, opens a visit, records chief complaint, history, examination, diagnosis (ICD-10, multi), clinical notes. |
| F-03-04 | OPD Prescription | Doctor writes a prescription: multiple Rx items with drug name, dose, frequency, duration, instructions. |
| F-03-05 | Investigation Order | Doctor orders lab / radiology tests from a catalogue (lab master table seeded). |
| F-03-06 | OPD Billing | Auto-generated bill per visit: consultation fee + Rx items + investigation items + additional charges. Supports cash/card/insurance payment recording. |
| F-03-07 | Printable Outputs | OPD ticket, prescription, and bill are printable on A4/A5 (browser print with print-optimised CSS). |
| F-03-08 | OPD Visit Lifecycle | `waiting → vitals_taken → in_consultation → completed → billed → closed`. Cancellation supported. |
| F-03-09 | OPD Reports & Dashboard | Daily OPD count, doctor-wise load, revenue summary, pending-bills list. |

### Out of Scope (deferred)

- IPD / admission
- Lab result entry (catalogue + order only; results come from a later LIS module)
- Pharmacy dispensing workflow (only Rx is stored)
- Insurance pre-authorisation integration
- SMS/Email of prescription (relies on Sprint 2 notification stubs)

---

## 2. Domain Model (high level)

```
OpdVisit (1) ─── (1) Appointment
   │                  Patient
   │                  Doctor (Employee)
   │                  Department
   │
   ├── (0..1) OpdVitals
   ├── (1..n) OpdDiagnosis
   ├── (0..1) OpdPrescription ── (1..n) OpdPrescriptionItem
   ├── (0..n) OpdInvestigationOrder ── (1..n) OpdInvestigationOrderItem
   └── (0..1) OpdBill ── (1..n) OpdBillItem ── (0..n) OpdBillPayment
```

---

## 3. Requirements (EARS-style)

### F-03-01 Visit Registration
1. WHEN a receptionist creates an OPD visit from a `confirmed`/`checked_in` appointment, THE HMS SHALL auto-link the visit to that appointment and mark the appointment status `in_consultation`.
2. WHEN a receptionist creates a walk-in OPD visit, THE HMS SHALL require patient_id, doctor_id, department_id, visit_type (`walk_in`), and generate a unique `opd_no` per day.
3. THE HMS SHALL generate `opd_no` using the existing `code_sequences` table (label `opd_visit`, format `OPD-YYYYMMDD-####`).

### F-03-02 Vitals
4. WHEN a nurse saves vitals, THE HMS SHALL accept systolic, diastolic (mmHg), pulse (bpm), temperature (°C), spo2 (%), weight (kg), height (cm), and auto-compute BMI.
5. THE HMS SHALL reject vitals with systolic < diastolic at the validator layer (422 response).

### F-03-03 Consultation
6. WHEN a doctor opens an OPD visit with status `vitals_taken` or `in_consultation`, THE HMS SHALL transition it to `in_consultation` and lock it for that doctor.
7. THE HMS SHALL allow at most 1 `in_consultation` visit per doctor at a time (enforced via composite unique partial index — see design.md).
8. THE HMS SHALL allow recording 0..n diagnoses per visit, each with ICD-10 code + description + type (primary/secondary).
9. WHEN a doctor clicks "Complete Consultation", THE HMS SHALL transition status to `completed` and stamp `consultation_end_at`.

### F-03-04 Prescription
10. THE HMS SHALL allow 0..n prescription items per prescription: drug_name (free text OR from catalogue), dose (mg/ml), frequency (OD/BD/TID/QID/HS/SOS), duration_days, route (oral/iv/im/sc/topical), instructions.
11. THE HMS SHALL auto-create a printable Rx when `completed`.

### F-03-05 Investigation
12. THE HMS SHALL provide a seeded `lab_tests` catalogue (item, code, default_price, sample_type, tat_hours).
13. WHEN a doctor orders an investigation, THE HMS SHALL create an `opd_investigation_orders` row per test with status `ordered`.

### F-03-06 Billing
14. WHEN a visit is `completed`, THE HMS SHALL auto-generate an `opd_bill` with line items: consultation fee (from `appointments.consultation_fee`), each Rx item (price from `application_settings` drug catalogue), each investigation (from `lab_tests.default_price`), plus any manual additions.
15. THE HMS SHALL support partial / full payment recording against a bill; bill status transitions `unpaid → partial → paid` (and `refunded`/`waived` per existing appointment enum).
16. THE HMS SHALL NOT allow closing a visit while its bill is `unpaid` (validator returns 422).

### F-03-07 Print
17. THE HMS SHALL provide three printable views: `/opd/visit/:id/ticket`, `/opd/visit/:id/prescription`, `/opd/visit/:id/bill` — each with print-optimised CSS (`@media print`) accessible from the consultation screen.
18. THE HMS SHALL include hospital header (logo, name, address — from `application_settings`), patient demographics, and a footer signature line on every print.

### F-03-08 Lifecycle
19. THE HMS SHALL enforce the state machine `waiting → vitals_taken → in_consultation → completed → billed → closed`, with allowed `cancelled` from any pre-billed state. All transitions SHALL be recorded in `opd_visit_audit_logs`.

### F-03-09 Reports
20. THE HMS SHALL provide endpoints:
    - `GET /opd/dashboard/today` — today's counts by status, revenue.
    - `GET /opd/report/doctor-wise?from=&to=` — per-doctor OPD count and revenue.
    - `GET /opd/report/pending-bills` — list of visits with bill_status=unpaid/partial.

---

## 4. Non-Functional

- **Permissions** follow the `auth:opd:*` pattern, scopes: `menuAccess, list, view, create, update, delete, bill, print`.
- **Performance** — all list endpoints paginated (page/per_page), indexed as described in `design.md`.
- **Audit** — every status transition + bill payment writes to `opd_visit_audit_logs`.
- **i18n** — English only this sprint; design leaves room for a future `messages.lang` join.
- **No new architecture** — reuses `Repository`, `API Resource`, `Validator`, `TraitRest*` patterns from Sprint 1/2.

---

## 5. Acceptance Criteria (summary)

- [ ] Creating a walk-in OPD visit generates `OPD-YYYYMMDD-####`.
- [ ] Linking a visit to a confirmed appointment auto-flips that appointment to `in_consultation`.
- [ ] Vitals form rejects `systolic < diastolic`.
- [ ] Only one visit per doctor can be `in_consultation` at any moment (DB-level guarantee).
- [ ] Completing a consultation auto-creates a bill with correct consultation fee + Rx + investigation line items.
- [ ] Bill prints on A4 with hospital header.
- [ ] Closing a visit with an unpaid bill returns 422.
- [ ] Doctor-wise report matches sum of doctor load across given date range.