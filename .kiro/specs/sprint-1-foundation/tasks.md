# Implementation Plan — Sprint 1: Foundation

- [x] 1. Fix and register Patient backend
- [x] 1.1 Fix the patients table migration
  - Remove duplicate `updated_by` column definition
  - Remove duplicate `status` column definition
  - Fix invalid index on non-existent `city`/`state` columns (replace with `current_city`, `current_state`)
  - _Requirements: 1.1, 1.5_

- [x] 1.2 Fix PatientController namespace and register patient routes in web.php
  - Change namespace in `PatientController.php` from `App\Http\Controllers` (it is inside a `patient/` subfolder but currently declares the wrong namespace — verify and correct)
  - Add patient route group to `backend/routes/web.php` with CRUD + bulk + dropdown endpoints
  - _Requirements: 1.1, 2.1, 2.5_

- [x] 1.3 Extend PatientRepository searchable fields and add duplicate-phone scope
  - Add `last_name` and `primary_phone` to `$fieldSearchable`
  - _Requirements: 2.1, 2.2_

- [x] 1.4 Update PatientValidator with unique phone rule and relax blood_group
  - On POST: add `primary_phone` unique rule against `patients` table
  - On PUT/PATCH: add `primary_phone` unique rule ignoring the current record id
  - Remove `blood_group` from required fields (make optional)
  - _Requirements: 1.1, 1.3_

- [x] 2. Build Patient frontend module
- [x] 2.1 Create Patient API service file
  - Create `frontend/src/app/api/Patient/Patient.api.ts` following the `Fack.api.ts` pattern
  - Endpoint base: `/patient`; include list, getById, create, update, updatePartial, delete, bulk, dropdown
  - Register `PatientApi` export in `frontend/src/app/api/index.tsx`
  - _Requirements: 1.1, 2.1_

- [x] 2.2 Create Patient Actions file
  - Create `frontend/src/app/modules/patient/components/Patient/Actions/Patient.actions.ts`
  - Define `COMMON_ACTION` (VIEW, CREATE), `LIST_ITEM_ACTION` (edit, delete), `BULK_ACTION`
  - Follow the `ExampleAction` pattern exactly
  - _Requirements: 1.1, 2.1_

- [x] 2.3 Create Patient List components (controller, filter, listing, pagination)
  - Copy and adapt from `modules/example/components/ExampleUser/List/`
  - List columns: MRN, Full Name, Gender, DOB, Primary Phone, Blood Group, Status, Actions
  - Filter: search (name/phone/MRN), gender filter, status filter
  - Wire `$filter` OData string: `first_name`, `last_name`, `primary_phone`, `mrn`, `gender`, `status`
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 2.4 Create Patient Form components (controller + tabbed form)
  - Create `PatientForm.controller.tsx` following `ExampleForm.controller.tsx` pattern
  - Create `PatientForm.form.tsx` with tabbed layout (Personal, Contact, Address, Medical, Insurance, Flags)
  - Required fields in form: first_name, last_name, date_of_birth, gender, primary_phone
  - Optional fields per section as described in design
  - _Requirements: 1.1, 1.2, 1.4_

- [x] 2.5 Create Patient View component and wire PatientRoutes + AdminRoutes
  - Create minimal `PatientView.controller.tsx` (drawer view pattern)
  - Create `PatientRoutes.tsx` with routes for list
  - Create `frontend/src/app/modules/patient/` module folder with `PatientRoutes.tsx`
  - Add lazy import and `<Route path='/patient/*'>` in `AdminRoutes.tsx`
  - _Requirements: 1.1, 2.1_

- [x] 3. Build Employee frontend module
- [x] 3.1 Create Employee API service file
  - Create `frontend/src/app/api/Company/Employee.api.ts` following existing API file patterns
  - Endpoint base: `/employee`; include all standard methods plus `getByUserId`
  - Register `EmployeeApi` export in `frontend/src/app/api/index.tsx`
  - _Requirements: 3.1, 3.5_

- [x] 3.2 Create Employee List, Form, and View components
  - Create `frontend/src/app/modules/company/components/Employee/` with Actions, List, Form, View subfolders
  - List columns: Employee ID, Name (EN), Designation, Mobile, Joining Date, Status, Actions
  - Filter: search (name/employee_id), designation filter, status filter
  - Form fields: name_en, name_bn, employee_id, designation_id, gender, mobile, dob, joining_date, employee_type, employee_category, status
  - _Requirements: 3.1, 3.2, 3.3, 3.5_

- [x] 3.3 Wire Employee into CompanyRoutes and AdminRoutes
  - Add `/employee` route to existing or new `CompanyRoutes.tsx` in `modules/company/`
  - Add lazy `CompanyRoutes` import and route path `/company/*` in `AdminRoutes.tsx` if not present
  - _Requirements: 3.1_

- [ ] 4. Verify RBAC and Master Data wiring
- [ ] 4.1 Verify RBAC routes and middleware are functional
  - Confirm `role`, `permission`, `resource`, `scope` routes all exist in `web.php` with `authVerify` middleware
  - Confirm frontend setting module exposes Role and Permission management pages
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ] 4.2 Verify Master Data routes and frontend setup module
  - Confirm `department`, `designation`, `unit`, `enum` routes exist in `web.php`
  - Confirm `SetupRoutes.tsx` exposes `/department`, `/designation`, `/unit` pages
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_
