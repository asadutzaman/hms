<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the role names the mobile role apps are gated on and attaches them to
 * the demo staff users.
 *
 * The mobile launcher ("Choose your session") and MobileRoleMiddleware both
 * match on the role NAME resolved by SessionService (role_name_list), e.g.
 * 'mobile.role:Doctor'. Without these roles every staff login lands on the
 * launcher with every app showing "No access", because the demo staff users are
 * seeded with role_ids = null.
 *
 * Role is derived from the user's `employees.employee_type`, which is already
 * seeded by MasterDataDemoSeeder / OpdDemoSeeder.
 *
 * Idempotent: roles are firstOrCreate'd on name and role ids are merged into the
 * user's existing role_ids. Safe to run repeatedly; truncates nothing.
 *
 *   php artisan db:seed --class=MobileRoleAssignmentSeeder
 *
 * NEVER run bare `php artisan db:seed` (AuthSeeder truncates users/roles).
 */
class MobileRoleAssignmentSeeder extends Seeder
{
    /** employee_type => role name the mobile apps gate on. */
    private const TYPE_ROLE_MAP = [
        'doctor'       => 'Doctor',
        'nurse'        => 'Nurse',
        'receptionist' => 'Administrator',
        'hr'           => 'Administrator',
        'accountant'   => 'Administrator',
    ];

    private const ROLE_DEFINITIONS = [
        'Doctor'        => ['code' => 'ROLE_DOCTOR', 'description' => 'Consultant / treating doctor'],
        'Nurse'         => ['code' => 'ROLE_NURSE', 'description' => 'Ward nursing staff'],
        'Administrator' => ['code' => 'ROLE_ADMINISTRATOR', 'description' => 'Hospital operations administrator'],
    ];

    public function run(): void
    {
        // ---- Roles ---------------------------------------------------------
        $roleIdByName = [];
        foreach (self::ROLE_DEFINITIONS as $name => $attributes) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                array_merge($attributes, ['status' => 1]),
            );
            $roleIdByName[$name] = (string) $role->id;
        }

        // ---- Attach to the staff users behind each employee record ---------
        $assigned = 0;

        $employees = Employee::query()
            ->whereNotNull('user_id')
            ->whereIn('employee_type', array_keys(self::TYPE_ROLE_MAP))
            ->get(['id', 'user_id', 'employee_type']);

        foreach ($employees as $employee) {
            $roleId = $roleIdByName[self::TYPE_ROLE_MAP[$employee->employee_type]];
            if ($this->attachRole($employee->user_id, $roleId)) {
                $assigned++;
            }
        }

        // ---- Users with no employee record but a clear role ----------------
        $nurseUserId = User::query()->where('email', 'nurse1@hms.local')->value('id');
        if ($nurseUserId && $this->attachRole($nurseUserId, $roleIdByName['Nurse'])) {
            $assigned++;
        }

        $this->command?->info(
            "MobileRoleAssignmentSeeder: roles Doctor/Nurse/Administrator ensured; {$assigned} user(s) updated."
        );
    }

    /** Merge $roleId into the user's role_ids. Returns true when the row changed. */
    private function attachRole(int $userId, string $roleId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $current = array_map('strval', (array) $user->role_ids);
        if (in_array($roleId, $current, true)) {
            return false;
        }

        $user->role_ids = array_values(array_unique(array_merge($current, [$roleId])));
        $user->save();

        return true;
    }
}
