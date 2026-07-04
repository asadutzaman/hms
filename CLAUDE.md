# HMS — Module Development Guide

Laravel 12 (Repository + API Resource pattern) backend, React 18 + TypeScript + Metronic frontend.
Auth, roles/permissions, workflow engine, and the Example CRUD module are DONE — never rebuild them.
Also read: `docs/AI_CONTEXT.md`, `docs/AI_RULES.md`, backlog in `docs/HMS_Product_Backlog.xlsx`.

## Building a new module — ALWAYS follow these steps

### Backend (`backend/`)

1. Generate the skeleton:
   ```
   php artisan imake:crud Sample Samples --all
   ```
   This creates Controller, Model, Repository, Resource, Validator, Migration.
2. Fill in required fields in the **migration** and **model** (fillable, relationships).
3. Update the Validator and Repository if needed.
4. Register routes in `backend/routes/web.php` (NOT api.php), following the existing pattern:
   ```php
   Route::group(['prefix' => 'sample', 'middleware' => ['restrictIp', 'authVerify']], function () {
       Route::post('/bulk', [App\Http\Controllers\SampleController::class, 'bulk']);
       Route::get('/dropdown', [App\Http\Controllers\SampleController::class, 'dropdown']);
       Route::get('/', ...); Route::get('/{id}', ...); Route::post('/', ...);
       Route::put('/{id}', ...); Route::patch('/{id}', ...); Route::delete('/{id}', ...);
   });
   ```
5. Create a permission JSON modeled on
   `backend/database/seeders/json/permission/auth/examplePermission.json`
   (resource block + scopes: menuAccess, list, view, create, edit, delete, multiSelect).
   **Place it in the correct domain folder** — one folder per domain:
   - `.../permission/hms/` → clinical modules (patient, appointment, opd, …)
   - `.../permission/inventory/` → inventory/procurement modules
   - `.../permission/auth/` → platform/admin modules
   Create a new folder if the module starts a new domain.

### Frontend (`frontend/`)

1. Copy `frontend/src/app/modules/example` (has Form, List/table, View components in the
   `Module/{Form,List,View}/*.controller.tsx|.form.tsx|.listing.tsx|.filter.tsx|.pagination.tsx` pattern).
2. Rename the folder and files to the module name.
3. Update form fields, table columns, view page, and validation.
4. Register API endpoints in `frontend/src/app/api/index.tsx`, matching the backend route prefix.
5. Register the module's routes file (e.g. `SampleRoutes.tsx`) in `frontend/src/app/routing/AdminRoutes.tsx`.
6. Add the menu entry in
   `frontend/src/_metronic/layout/components/sidebar/sidebar-menu/LeftSidebar.menu.tsx`.

### Dropdowns

If the module needs a select/dropdown of another entity, copy the existing pattern:
- hook: `frontend/src/app/hooks/lists/useOrganizationList.tsx`
- component: `frontend/src/app/components/Dropdown/OrganizationSelect.tsx`
Name them `use<Entity>List.tsx` and `<Entity>Select.tsx`.

## Rules

- Analyze the Example module and an existing real module (e.g. patient or supplier) before coding.
- Reuse existing common form/table components, hooks, validators, response structure.
- No new architecture, state management, or UI libraries. Follow Metronic styling.
- Migrations: check existing tables first; add indexes and foreign keys; use soft deletes (project standard).
- Before coding, output: requirement analysis, DB changes, API endpoints, affected files, implementation plan.
