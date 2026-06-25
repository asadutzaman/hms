# Requirements Document — Sprint 1: Foundation

## Introduction

Sprint 1 establishes the foundational layer of the Hospital Management System (HMS). It covers patient identity management with auto-generated Unique Hospital IDs (UHID), patient search, employee master data, Role-Based Access Control (RBAC), and master data management (departments, designations, units, enumerations). These five features form the backbone that every subsequent sprint depends on.

The system is built on a Laravel 12 backend (Repository Pattern, API Resource Pattern) and a React + TypeScript frontend using the Metronic theme.

---

## Glossary

- **HMS**: Hospital Management System — the software platform being developed.
- **UHID**: Unique Hospital ID — an auto-generated, system-wide unique alphanumeric identifier assigned to every patient at first registration.
- **MRN**: Medical Record Number — a sequential numeric identifier stored alongside the UHID for internal record-keeping.
- **Patient**: A person who registers at the hospital to receive medical services.
- **Employee**: A hospital staff member (doctor, nurse, admin, technician, etc.) whose profile is managed in the system.
- **RBAC**: Role-Based Access Control — a security model in which access to system resources is governed by roles assigned to users.
- **Role**: A named collection of permissions assigned to one or more users.
- **Permission**: An atomic grant allowing a user or role to perform a specific action on a specific resource.
- **Master Data**: Reference data (departments, designations, blood groups, gender enumerations, units, etc.) used across all modules.
- **Department**: An organisational unit within the hospital (e.g., Cardiology, Radiology, Administration).
- **Designation**: A job title or post held by an employee (e.g., Senior Doctor, Staff Nurse, Lab Technician).
- **Duplicate Check**: A process that detects whether a patient record with matching identifiers (phone, name + DOB) already exists before creating a new record.
- **Soft Delete**: A deletion pattern in which a record is flagged as deleted (`deleted_at`) rather than removed from the database.
- **API Resource**: A Laravel resource class that transforms an Eloquent model into a standardised JSON response.
- **Repository**: A Laravel repository class that encapsulates all data-access logic for a model.

---

## Requirements

### Requirement 1 — Patient Registration & UHID Generation (F-01-01)

**User Story:** As a reception staff member, I want to register a new patient and receive an auto-generated UHID, so that every patient has a unique, traceable identity in the system.

#### Acceptance Criteria

1. WHEN a staff member submits a new patient registration form with first name, last name, date of birth, gender, blood group, and primary phone, THE HMS SHALL create a patient record and assign a unique UHID.
2. WHEN the HMS generates a UHID, THE HMS SHALL use an auto-incrementing integer MRN stored in the `patients` table with a unique index.
3. IF a patient record already exists with the same primary phone number, THEN THE HMS SHALL return a validation error stating "A patient with this phone number already exists."
4. WHEN a patient record is created, THE HMS SHALL record the `registration_date` as the current timestamp.
5. WHILE a patient record exists in the system, THE HMS SHALL support soft delete so that deletion flags the record without removing it from the database.

---

### Requirement 2 — Patient Search & Advanced Filter (F-01-02)

**User Story:** As a reception staff member, I want to search for patients by name, phone, or UHID with advanced filters, so that I can quickly locate an existing patient record.

#### Acceptance Criteria

1. WHEN a staff member enters a search term, THE HMS SHALL return matching patient records filtered by first name, last name, primary phone, or MRN within a single API call.
2. WHEN a staff member applies an advanced filter by date of birth, gender, or status, THE HMS SHALL return only records matching all applied filter criteria.
3. WHEN the search query returns results, THE HMS SHALL return the response with pagination supporting configurable page size (default 10 records per page).
4. IF no records match the search criteria, THEN THE HMS SHALL return an empty data array with a total count of zero.
5. WHERE the OData query service is available, THE HMS SHALL support `$filter`, `$search`, `$orderby`, `$top`, and `$skip` parameters in the patient list endpoint.

---

### Requirement 3 — Employee Master (F-13-01)

**User Story:** As an HR administrator, I want to create and manage employee profiles with department, designation, and contact details, so that hospital staff are identifiable and assignable to roles in the system.

#### Acceptance Criteria

1. WHEN an HR administrator submits a new employee record with full name, department, designation, employee code, and primary phone, THE HMS SHALL create the employee record and return the saved data.
2. WHEN an employee record is created or updated, THE HMS SHALL enforce uniqueness on the employee code field.
3. WHILE an employee record exists in the system, THE HMS SHALL allow updating contact details, department, and designation without recreating the record.
4. IF an employee code already exists in the system, THEN THE HMS SHALL return a validation error stating "Employee code already exists."
5. WHEN an HR administrator requests the employee list, THE HMS SHALL support filtering by department, designation, and status, and return paginated results.

---

### Requirement 4 — Role-Based Access Control (F-15-01)

**User Story:** As a system administrator, I want to define roles and assign permissions to them, so that each user has access only to the system resources their role permits.

#### Acceptance Criteria

1. WHEN a system administrator creates a role with a unique name, THE HMS SHALL persist the role and make it available for permission assignment.
2. WHEN a system administrator assigns permissions to a role, THE HMS SHALL associate each selected scope action with the role and persist the mapping.
3. WHEN a user logs in, THE HMS SHALL load the user's assigned roles and resolve all associated permissions for the current session.
4. WHILE a user session is active, THE HMS SHALL enforce permission checks on every protected API endpoint using the `authVerify` middleware.
5. IF a user attempts to access an endpoint for which the user's role has no permission, THEN THE HMS SHALL return an HTTP 403 response with an appropriate error message.

---

### Requirement 5 — Master Data Management (F-15-03)

**User Story:** As a system administrator, I want to manage master data (departments, designations, units, blood groups, gender options, and enumerations), so that all modules in the system use consistent reference data.

#### Acceptance Criteria

1. WHEN a system administrator creates a department record with a unique name, THE HMS SHALL persist the department and make it available in all department dropdown endpoints.
2. WHEN a system administrator creates a designation record linked to a department, THE HMS SHALL persist the designation and return it in designation dropdown endpoints.
3. WHEN a system administrator creates a unit of measure record, THE HMS SHALL persist the unit and return it in the unit dropdown endpoint.
4. IF a duplicate department name is submitted, THEN THE HMS SHALL return a validation error stating "Department name already exists."
5. WHERE the enum management feature is available, THE HMS SHALL allow administrators to create and retrieve enumeration entries (blood group, gender, religion, marital status) used across patient and employee forms.
