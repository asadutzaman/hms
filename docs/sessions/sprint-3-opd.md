# Sprint 3 — OPD Management — Session Log

## Status: **Phases A–C DONE**, Phase D (models / repos / validators / resources / services) next.

---

## Phase A — Spec & Approval ✅

Created `.kiro/specs/sprint-3-opd/{requirements.md, design.md, tasks.md}` from the
sprint brief. Plan covers 9 features (F-03-01 … F-03-09), 12 tables, the visit
state machine, billing snapshot, browser-print strategy, and the one-active-
per-doctor concurrency guard.

---

## Phase B — Backend Skeleton ✅

Ran `php artisan imake:crud` for all 11 modules (10 OPD entities + `LabTest`).
Generated models, repositories, validators, resources, controllers, and stub
migrations. Files live under `backend/app/{Http/Controllers,Models,Repositories,
Validators,Http/Resources}` and `backend/database/migrations/2026_06_26_*.php`.

---

## Phase C — Database ✅

### C.1 — Migration rewrites

Replaced each imake:crud stub with the schema from `design.md` §2.1:

| Table | Migration timestamp | Notes |
|---|---|---|
| `opd_visits` | `…191352` | encounter header; FKs to patients/appointments/employees (deferred via C.4) |
| `opd_vitals` | `…191353` | 1:1 with visit |
| `opd_diagnoses` | `…191554` | multi-row per visit |
| `opd_prescriptions` | `…191555` | 1:1 with visit |
| `opd_prescription_items` | `…191556` | multi-row per Rx |
| `opd_investigation_orders` | `…191557` | 0..n per visit |
| `opd_investigation_order_items` | `…191558` | line items, FK `lab_tests.id` |
| `opd_bills` | `…191559` | 1:1 with visit |
| `opd_bill_items` | `…191559` | snapshot-priced line items |
| `opd_bill_payments` | `…191559` | partial payments allowed |
| `opd_visit_audit_logs` | `…191636` | status transitions + edits |
| `lab_tests` | `…191352` | catalogue |
| `opd_visit_sequences` | `…191353` | per-day OPD No counter |

Seven of the rewritten migrations had trailing junk left over by imake:crud
(`@return void` docblock + duplicate `down()` outside the class brace). Removed.

### C.2 — Indexes

Standard B-tree indexes added per `design.md` §2.2 (doctor_id, status,
patient_id, visit_date, code, etc.). The Postgres-only partial unique index
`idx_opd_visits_one_active_per_doctor` (design §1.4) was **not** emitted —
prod is MySQL (verified via `.env`). The repo-level `SELECT … FOR UPDATE`
guard (D.5) gives the same guarantee on MySQL.

### C.3 — `code_sequences.label = opd_visit`

No new migration needed. Sprint 1/2 already extended the enum; `CodeSequenceService`
will accept `'opd_visit'` once Phase D wires it.

### C.4 — Deferred employee FKs

`2026_06_26_200001_add_opd_employee_fks_when_employees_exists.php` —
idempotent ALTERs that attach `opd_visits.doctor_id` → `employees.id` ON DELETE
RESTRICT, and all actor columns (created_by, updated_by, closed_by, prescribed_by,
ordered_by, billed_by, paid_by, audit.actor_id) → ON DELETE SET NULL. Same shape
as Sprint 2's appointment FK migration.

### C.5 — Lab test seeder

`database/seeders/LabTestSeeder.php` seeds 34 common tests across 7 categories:
HEM (7), BIO (11), URN (2), MIC (3), SER (4), RAD (5), CAR (2). Uses
`LabTest::updateOrCreate(['code' => …])` so the `Uuid` trait auto-fills.

**Bug encountered**: imake:crud generated `lab_tests` without the standard
`status` column that `BaseModel::$attributes['status'] = StatusEnum::ACTIVE`
expects. Attempting `LabTest::create()` therefore failed at save time
(`Column not found: 1054 Unknown column 'status'`).

**Fix**: small follow-up migration `2026_06_27_120000_add_status_to_lab_tests_table.php`
adds `status tinyInteger default 1` after `status_flag`. The `LabTest` model
was also rewritten to populate `$fillable`, `$casts`, and default attributes
for the catalogue columns.

### C.6 — Migrate + Seed

```
$ php artisan migrate
… 14 migrations DONE
$ php artisan db:seed --class=LabTestSeeder
… (silent; check count)
$ php artisan tinker --execute="echo App\Models\LabTest::count();"
34
```

Snapshot (all OPD tables empty, lab_tests seeded):

```
opd_visits                             rows=0
opd_vitals                             rows=0
opd_diagnoses                          rows=0
opd_prescriptions                      rows=0
opd_prescription_items                 rows=0
opd_investigation_orders               rows=0
opd_investigation_order_items          rows=0
opd_bills                              rows=0
opd_bill_items                         rows=0
opd_bill_payments                      rows=0
opd_visit_audit_logs                   rows=0
opd_visit_sequences                    rows=0
lab_tests                              rows=34
```

---

## Deviations from design

- **No Postgres partial unique index.** `.env` has `DB_CONNECTION=mysql`. Concurrency
  guarantee moves to `OpdVisitRepository::transitionStatus()` using
  `SELECT … FOR UPDATE` (D.5).
- **Added `status` column to `lab_tests`.** Not in design §2.1, but required by
  `BaseModel` convention. Treated as additive — no behaviour change.

---

## What's next — Phase D

Customize all 11 generated models, repositories, validators, and resources,
then write the two services:

- `OpdVisitService` — status machine, opd_no generation (atomic
  `opd_visit_sequences` increment), `assertOnlyOneActiveForDoctor` guard,
  bill generation snapshot.
- `OpdBillService` — payment recording + `unpaid → partial → paid` rollup.