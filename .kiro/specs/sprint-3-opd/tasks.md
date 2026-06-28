# Sprint 3 — OPD Implementation Tasks

Generated from `requirements.md` and `design.md`. Execute top-to-bottom; each task is a checkpoint for the user.

> **Rule from `docs/AI_RULES.md`**: produce the plan (this file) and wait for approval before coding. Approval is requested at the bottom.

---

## Phase A — Spec & Approval ✅

- [x] **A1** — Create `.kiro/specs/sprint-3-opd/{requirements.md, design.md, tasks.md}` ✅ (this file)
- [x] **A2** — User reviewed and approved plan.
- [x] **A3** — Create `docs/sessions/sprint-3-opd.md` session log (update at end).

## Phase B — Backend Skeleton ✅

- [x] **B1** — Run `imake:crud` for each of the 11 modules (see design §5).
- [x] **B2** — Sanity-check generated files exist:
  - `backend/app/Http/Controllers/{OpdVisitController, OpdVitalController, OpdDiagnosisController, OpdPrescriptionController, OpdPrescriptionItemController, OpdInvestigationOrderController, OpdInvestigationOrderItemController, OpdBillController, OpdBillItemController, OpdBillPaymentController, LabTestController}.php`
  - `backend/app/Models/{OpdVisit, OpdVital, OpdDiagnosis, OpdPrescription, OpdPrescriptionItem, OpdInvestigationOrder, OpdInvestigationOrderItem, OpdBill, OpdBillItem, OpdBillPayment, LabTest}.php`
  - `backend/database/migrations/2026_07_*_create_*_tables.php` (11 files)

## Phase C — Database ✅

- [x] **C1** — Update each generated migration with the actual schema from `design.md` §2.1.
- [x] **C2** — Add indexes (design §2.2). Partial unique index `idx_opd_visits_one_active_per_doctor` declared in Postgres section of `design.md` §1.4; Postgres not the prod target — DB-level guard deferred to repo via `SELECT FOR UPDATE` (Phase D).
- [x] **C3** — `code_sequences.label` already extended to include `opd_visit` by Sprint 1/2 code-sequence migrations; verified via inspection (no separate add-opd migration needed; `CodeSequenceService::next('opd_visit', today)` will work once Phase D wires it).
- [x] **C4** — Idempotent migration `2026_06_26_200001_add_opd_employee_fks_when_employees_exists.php` applied.
- [x] **C5** — `LabTestSeeder` written; follow-up migration `2026_06_27_120000_add_status_to_lab_tests_table.php` adds the standard `status` column `BaseModel` expects (imake:crud stub had omitted it). 34 catalogue rows seeded.
- [x] **C6** — `php artisan migrate` green; 14 OPD migrations + lab_tests + status column all applied; 34 lab_tests rows present.

## Phase D — Models, Repositories, Validators, Resources

- [ ] **D1** — Set `$fillable` on each model per design §2.1.
- [ ] **D2** — Define relationships on `OpdVisit` (`patient`, `appointment`, `doctor`, `department`, `vitals`, `diagnoses`, `prescription`, `prescription.items`, `investigationOrders.items`, `bill`, `bill.items`, `bill.payments`, `auditLogs`).
- [ ] **D3** — Add `casts` (`visit_date` date, timestamps → datetime, `payload` → array on audit log).
- [ ] **D4** — Implement `OpdVisitRepository::transitionStatus($visit, $toStatus, $actorId, $payload = null)` enforcing the state machine in design §1.2 and writing audit log in same transaction. Throw `OpdInvalidTransitionException` on illegal moves.
- [ ] **D5** — Implement `OpdVisitRepository::assertOnlyOneActiveForDoctor($doctorId)` using `SELECT ... FOR UPDATE` (MySQL) — used inside `transition()`.
- [ ] **D6** — Implement `OpdVisitService::generateBill($visitId)` building bill + items from `consultation_fee`, prescription items (price from `application_settings.drug_catalogue` key), investigation items (price snapshot from `lab_tests`), manual extras. Idempotent (returns existing bill if already generated).
- [ ] **D7** — Implement `OpdBillService::recordPayment($billId, $amount, $method, $refNo, $paidBy)` updating bill status (`unpaid → partial → paid`).
- [ ] **D8** — Validators per `requirements.md` (vitals range, systolic ≥ diastolic, ICD-10 format regex, bill close requires paid/waived).
- [ ] **D9** — Resources expose nested data per design §3.

## Phase E — Controllers & Routes

- [ ] **E1** — Each generated controller already has the 7 `TraitRest*` actions. Add module-specific:
  - `OpdVisitController::today`, `::transition`, `::cancel`
  - `OpdBillController::generate($visitId)`, `::recordPayment`, `::waive`
  - `OpdReportController::dashboardToday`, `::doctorWise`, `::pendingBills`, `::revenue`
- [ ] **E2** — Register routes in `backend/routes/web.php` (mirroring `appointment` route group).
- [ ] **E3** — Register print routes (`/opd/print/visit/{id}/{ticket|prescription|bill}`) in `backend/routes/web.php` (under same `auth` middleware group but OUTSIDE `/api/auth` prefix — they return HTML).
- [ ] **E4** — Add `OpdVisitService` and `OpdBillService` to `backend/app/Providers/AppServiceProvider.php` if needed (or use auto-resolution).

## Phase F — Permissions

- [ ] **F1** — Create `backend/database/seeders/json/permission/hms/opdPermission.json` with scopes from `design.md` §6.
- [ ] **F2** — Register the seeder call in `backend/database/seeders/PermissionSeeder.php` (verify exact path used by Sprint 2).
- [ ] **F3** — Run `php artisan db:seed --class=PermissionSeeder` and verify scopes appear in DB.

## Phase G — Print Views

- [ ] **G1** — Create `resources/views/opd/print/ticket.blade.php` with header (hospital logo, name, address from `application_settings`), patient demographics, OPD No, doctor, dept, date, token.
- [ ] **G2** — Create `resources/views/opd/print/prescription.blade.php` with Rx header + items table.
- [ ] **G3** — Create `resources/views/opd/print/bill.blade.php` with bill no, items, totals, payments, balance.
- [ ] **G4** — Create `resources/views/opd/print/_layout.blade.php` shared by all three with `@media print` CSS (A4 portrait, 10mm margins, no nav).
- [ ] **G5** — Verify each route returns 200 + HTML by hitting it locally with a valid session cookie.

## Phase H — Frontend Module

- [ ] **H1** — Copy `frontend/src/app/modules/appointment/` skeleton → `frontend/src/app/modules/opd/`. Rename imports/files. **No new UI library**.
- [ ] **H2** — Build API wrappers in `frontend/src/app/api/index.tsx` (see design §4.4).
- [ ] **H3** — `OpdVisitList.tsx` — datatable with filters (date range, doctor, status, patient), columns: OPD No, Patient, Doctor, Status, Bill Status, Created.
- [ ] **H4** — `OpdVisitForm.tsx` (create) — receptionist flow: pick from-appointment OR walk-in mode. Submits to `POST /opd-visit`.
- [ ] **H5** — `OpdVisitView.tsx` — tabbed: Overview / Vitals / Diagnosis / Prescription / Investigation / Bill. Each tab loads its own section lazily.
- [ ] **H6** — `OpdVisitForm.controller.tsx` (edit) — doctor's flow: edit chief complaint, history, examination, diagnosis (multi), prescription (multi), investigation orders. Triggers status transitions via `POST /opd-visit/{id}/transition`.
- [ ] **H7** — `OpdQueueBoard.tsx` — doctor's live queue (polling every 10 s; reuse pattern from `appointment/components/Queue/`).
- [ ] **H8** — `OpdDashboard.tsx`, `DoctorWiseReport.tsx`, `PendingBillsReport.tsx` — reuse chart/reporting patterns from `frontend/src/app/modules/reports/`.
- [ ] **H9** — `PrintButton.tsx` — opens the print URL in a new tab.

## Phase I — Routing & Menu

- [ ] **I1** — Create `frontend/src/app/modules/opd/OPDRoutes.tsx` exporting the route config.
- [ ] **I2** — Register the route group in `frontend/src/app/routing/AppRoutes.tsx` (verify exact path from Sprint 2's `AppointmentRoutes.tsx`).
- [ ] **I3** — Add OPD menu items in the sidebar config (verify exact file under `frontend/src/_metronic/layout/`).

## Phase J — Verification

- [ ] **J1** — Feature test `backend/tests/Feature/OpdVisitTest.php` covering:
  - create from confirmed appointment
  - create walk-in (opd_no generated)
  - vitals save + invalid (systolic < diastolic → 422)
  - status transition matrix (legal & illegal moves)
  - only-one-active-per-doctor (two simultaneous transitions → 409)
  - bill generation + payment recording
  - close while unpaid → 422
  - print route returns 200 HTML
- [ ] **J2** — Run `php artisan test` — all green.
- [ ] **J3** — Manual walkthrough of the acceptance criteria in `requirements.md` §5.
- [ ] **J4** — Update `docs/sessions/sprint-3-opd.md` with what was built, deviations, known gaps.

---

## ⏸️ APPROVAL REQUESTED

This plan is complete. Per `docs/AI_RULES.md` I will not write any code until you confirm:

1. **Scope** — The 9 features (F-03-01 … F-03-09) and the 12 tables in §2.1 match what you want.
2. **Print strategy** — Browser-print via Blade views + `window.open` (no PDF lib). If you need a PDF (e.g. `barryvdh/laravel-dompdf`), say so now.
3. **One-active-visit-per-doctor** — DB partial index on Postgres, repo-level `FOR UPDATE` on MySQL. If your prod is MySQL/MariaDB only, I can drop the partial-index migration.
4. **Billing integration** — Bills link to `appointments.consultation_fee` for the consultation line. Drug prices come from `application_settings.drug_catalogue` (a JSON column we will seed). Investigation prices from `lab_tests.default_price`. If you have a separate `drug_catalogue` table plan, mention it now so we adjust.
5. **Permissions split** — `auth:opd:{menuAccess,list,view,create,update,delete,bill,print}` matches your intended role policy. Adjust if you want doctor/nurse/receptionist fine-grained roles instead.

Reply **"approved"** (or with edits) and I will begin Phase B.