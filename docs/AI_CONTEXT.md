# Project Development Rules

## Project Stack

Backend:

* Laravel 12
* Repository Pattern
* API Resource Pattern

Frontend:

* React
* TypeScript
* Metronic Theme

## Existing Features

The following are already implemented and should be reused:

* Authentication
* Role & Permission Management
* Example CRUD Module
* Common Form Components
* Common Table Components
* API Service Structure
* Validation Structure
* Repository Structure

## Module Development Process

Whenever a new module is requested, follow these steps.

### Backend

Generate the module skeleton using:

php artisan imake:crud ModuleName ModuleNames --all

This command creates:

* Controller
* Model
* Repository
* Resource
* Validator
* Migration

After generation:

1. Update Migration fields according to requirements.
2. Update Model fillable fields.
3. Define relationships.
4. Update validation rules.
5. Update repository methods if needed.
6. Register routes in web.php.
7. Register permissions if required.

Do not create custom architecture.
Always follow existing project structure.

### Frontend

Use the Example Module as the base template.

Steps:

1. Copy Frontend/src/app/modules/example
2. Rename folder and files according to module name.
3. Update:

   * Form fields
   * Table columns
   * View page
   * Validation
4. Update Frontend/src/app/api/index.tsx
5. Configure API endpoints according to backend routes.
6. Reuse existing common components whenever possible.

Do not introduce new UI frameworks.
Follow existing Metronic UI patterns.

## Development Rules

Before implementing:

1. Analyze Example Module.
2. Analyze existing coding patterns.
3. Follow naming conventions.
4. Reuse existing components.
5. Minimize code duplication.

## Required Output

Before coding:

1. Analyze requirements.
2. List affected files.
3. List database changes.
4. List API endpoints.
5. Create implementation plan.

Then generate code.
