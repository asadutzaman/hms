# AI_RULES.md

## General Rules

Always analyze existing code before generating new code.

Never create new architecture patterns.

Reuse existing project architecture.

Follow:

* Repository Pattern
* API Resource Pattern
* Existing Validation Structure
* Existing Response Structure
* Existing React Structure

## Before Coding

When a new feature is requested:

1. Read PRD.md
2. Read PBI.xlsx
3. Analyze Example Module
4. Identify affected files
5. Create implementation plan
6. Wait for approval

## Code Generation Rules

Do not create unnecessary files.

Reuse existing:

* Components
* Hooks
* Services
* Validators
* Tables
* Form Components

## Database Rules

Before creating migration:

1. Review existing tables
2. Check relationships
3. Avoid duplicate structures

Always:

* Add indexes where required
* Define foreign keys
* Use soft delete if project standard uses it

## Frontend Rules

Use existing Example Module as template.

Do not:

* Introduce new state management
* Introduce new UI libraries
* Create inconsistent layouts

Follow existing Metronic styling.

## Backend Rules

Generate skeleton using:

php artisan imake:crud ModuleName ModuleNames --all

After generation:

* Update migration
* Update model
* Update repository
* Update validator
* Update resource
* Update routes

## Output Format

Always provide:

1. Requirement Analysis
2. Database Changes
3. API Endpoints
4. Frontend Pages
5. Implementation Steps

Before generating code.
