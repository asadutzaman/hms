<?php

namespace Tests\Feature;

use App\Http\Controllers\DoctorScheduleController;
use App\Models\AppointmentSlot;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\DoctorScheduleSlot;
use App\Models\User;
use App\Repositories\DoctorScheduleRepository;
use App\Repositories\UserRepository;
use App\Validators\DoctorScheduleValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Tests\TestCase;

/**
 * Integration tests for the Doctor Schedule module. They run against the
 * configured (dev) Postgres DB and roll every write back via
 * DatabaseTransactions, so no data leaks. The DB already has the schema +
 * seeded reference rows (employees, departments) we reference for FKs.
 */
class DoctorScheduleTest extends TestCase
{
    use DatabaseTransactions;

    private DoctorScheduleRepository $repo;
    private int $doctorId;
    private ?int $departmentId;
    private string $date = '2026-08-10'; // a fixed Monday
    private int $dow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new DoctorScheduleRepository();
        // doctor_id now references users (was employees).
        $this->doctorId = (int) User::query()->where('id', '!=', 1)->value('id');
        $this->departmentId = Department::query()->value('id');
        $this->dow = Carbon::parse($this->date)->dayOfWeek; // 0=Sun..6=Sat
    }

    /* ---------- fixtures ---------- */

    private function makeSchedule(array $overrides = []): DoctorSchedule
    {
        return DoctorSchedule::create(array_merge([
            'doctor_id'             => $this->doctorId,
            'department_id'         => $this->departmentId,
            'name'                  => 'Test Schedule',
            'schedule_type'         => 'regular',
            'consultation_mode'     => 'in_person',
            'effective_from'        => '2026-01-01',
            'effective_to'          => '2026-12-31',
            'slot_duration_minutes' => 30,
            'max_patients_per_slot' => 1,
            'buffer_minutes'        => 0,
            'is_default'            => true,
            'status'                => 1,
        ], $overrides));
    }

    private function makeSlot(DoctorSchedule $schedule, ?int $dow = null, array $overrides = []): DoctorScheduleSlot
    {
        return DoctorScheduleSlot::create(array_merge([
            'doctor_schedule_id' => $schedule->id,
            'day_of_week'        => $dow ?? $this->dow,
            'start_time'         => '09:00',
            'end_time'           => '12:00',
            'is_active'          => true,
            'status'             => 1,
        ], $overrides));
    }

    private function makeException(string $type, array $overrides = []): DoctorScheduleException
    {
        return DoctorScheduleException::create(array_merge([
            'doctor_id'       => $this->doctorId,
            'exception_date'  => $this->date,
            'exception_type'  => $type,
            'approval_status' => 'approved',
            'status'          => 1,
        ], $overrides));
    }

    /* ---------- smoke ---------- */

    public function test_database_connection_is_available(): void
    {
        $this->assertEquals(1, DB::select('select 1 as v')[0]->v);
        $this->assertNotEmpty($this->doctorId, 'Expected at least one seeded employee to act as a doctor.');
    }

    /* ---------- materializeSlotsForDate ---------- */

    public function test_materialize_generates_expected_slots_for_a_normal_window(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule); // 09:00-12:00, 30-min, 0 buffer

        $slots = $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $this->assertCount(6, $slots);
        $starts = array_map(fn ($s) => substr($s->start_time, 0, 5), $slots);
        $this->assertEquals(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'], $starts);
        $this->assertEquals('open', $slots[0]->status);
    }

    public function test_buffer_minutes_reduce_slot_count(): void
    {
        $schedule = $this->makeSchedule(['buffer_minutes' => 30]);
        $this->makeSlot($schedule); // 30-min slot + 30-min buffer => 60-min spacing

        $slots = $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $starts = array_map(fn ($s) => substr($s->start_time, 0, 5), $slots);
        $this->assertEquals(['09:00', '10:00', '11:00'], $starts);
    }

    public function test_no_window_for_day_returns_empty(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule, ($this->dow + 1) % 7); // a different weekday

        $this->assertSame([], $this->repo->materializeSlotsForDate($this->doctorId, $this->date));
    }

    public function test_date_outside_effective_range_returns_empty(): void
    {
        $schedule = $this->makeSchedule(['effective_to' => '2026-06-30']); // date is Aug
        $this->makeSlot($schedule);

        $this->assertSame([], $this->repo->materializeSlotsForDate($this->doctorId, $this->date));
    }

    public function test_leave_exception_blocks_every_slot(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);
        $this->makeException('leave'); // whole-day, no times

        $slots = $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $this->assertCount(6, $slots);
        foreach ($slots as $slot) {
            $this->assertTrue((bool) $slot->is_blocked);
            $this->assertEquals('blocked', $slot->status);
        }
    }

    public function test_time_ranged_block_exception_blocks_only_overlapping_slots(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);
        $this->makeException('blocked', ['start_time' => '10:00', 'end_time' => '11:00']);

        $slots = $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $blocked = array_values(array_filter($slots, fn ($s) => $s->is_blocked));
        $blockedStarts = array_map(fn ($s) => substr($s->start_time, 0, 5), $blocked);
        $this->assertEquals(['10:00', '10:30'], $blockedStarts);
    }

    public function test_extra_slot_exception_bumps_capacity_on_overlapping_slot(): void
    {
        $schedule = $this->makeSchedule(['max_patients_per_slot' => 1]);
        $this->makeSlot($schedule);
        $this->makeException('extra_slot', ['start_time' => '10:00', 'end_time' => '11:00']);

        $slots = $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $byStart = [];
        foreach ($slots as $s) {
            $byStart[substr($s->start_time, 0, 5)] = $s;
        }
        $this->assertEquals(1, (int) $byStart['09:00']->max_patients);
        $this->assertEquals(2, (int) $byStart['10:00']->max_patients);
    }

    public function test_materialize_is_idempotent(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);

        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);
        $countAfterFirst = AppointmentSlot::where('doctor_id', $this->doctorId)
            ->whereDate('slot_date', $this->date)->count();

        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);
        $countAfterSecond = AppointmentSlot::where('doctor_id', $this->doctorId)
            ->whereDate('slot_date', $this->date)->count();

        $this->assertEquals(6, $countAfterFirst);
        $this->assertEquals($countAfterFirst, $countAfterSecond, 'Re-running must not duplicate slots.');
    }

    /* ---------- reserveSlot / releaseSlot ---------- */

    public function test_reserve_slot_increments_and_fills(): void
    {
        $schedule = $this->makeSchedule(['max_patients_per_slot' => 1]);
        $this->makeSlot($schedule);
        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $slot = $this->repo->reserveSlot($this->doctorId, $this->date, '09:00:00');

        $this->assertEquals(1, $slot->booked_count);
        $this->assertEquals('full', $slot->status);
    }

    public function test_reserving_a_full_slot_throws(): void
    {
        $schedule = $this->makeSchedule(['max_patients_per_slot' => 1]);
        $this->makeSlot($schedule);
        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);
        $this->repo->reserveSlot($this->doctorId, $this->date, '09:00:00'); // now full

        $this->expectException(RuntimeException::class);
        $this->repo->reserveSlot($this->doctorId, $this->date, '09:00:00');
    }

    public function test_reserving_a_blocked_slot_throws(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);
        $this->makeException('leave');
        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);

        $this->expectException(RuntimeException::class);
        $this->repo->reserveSlot($this->doctorId, $this->date, '09:00:00');
    }

    public function test_release_slot_frees_capacity(): void
    {
        $schedule = $this->makeSchedule(['max_patients_per_slot' => 1]);
        $this->makeSlot($schedule);
        $this->repo->materializeSlotsForDate($this->doctorId, $this->date);
        $slot = $this->repo->reserveSlot($this->doctorId, $this->date, '09:00:00');

        $this->repo->releaseSlot($slot->id);

        $slot->refresh();
        $this->assertEquals(0, $slot->booked_count);
        $this->assertEquals('open', $slot->status);
    }

    /* ---------- query helpers ---------- */

    public function test_query_helpers_return_expected_rows(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);
        $this->makeException('leave');

        $this->assertEquals($schedule->id, $this->repo->getDefaultForDoctor($this->doctorId)->id);
        $this->assertCount(1, $this->repo->getRecurringWindows($schedule->id));
        $this->assertCount(1, $this->repo->getExceptionsForDate($this->doctorId, $this->date));
    }

    /* ---------- models ---------- */

    public function test_schedule_defaults_and_relation(): void
    {
        $schedule = $this->makeSchedule();
        $slot = $this->makeSlot($schedule);

        $this->assertEquals(1, $schedule->status);
        $this->assertIsBool($schedule->is_default);
        $this->assertTrue($schedule->slots->contains('id', $slot->id));
    }

    public function test_schedule_slot_soft_deletes(): void
    {
        $schedule = $this->makeSchedule();
        $slot = $this->makeSlot($schedule);

        $slot->delete();

        $this->assertNull(DoctorScheduleSlot::find($slot->id));
        $this->assertTrue(DoctorScheduleSlot::withTrashed()->find($slot->id)->trashed());
    }

    /* ---------- validator ---------- */

    private function postRules(): array
    {
        $this->app->instance('request', Request::create('/api/doctor-schedule', 'POST'));
        return (new DoctorScheduleValidator())->rules();
    }

    public function test_validator_accepts_a_valid_payload(): void
    {
        $rules = $this->postRules();
        $payload = [
            'doctor_id'             => $this->doctorId,
            'name'                  => 'Morning OPD',
            'effective_from'        => '2026-01-01',
            'slot_duration_minutes' => 30,
            'max_patients_per_slot' => 2,
        ];
        $this->assertFalse(Validator::make($payload, $rules)->fails());
    }

    public function test_validator_rejects_missing_and_out_of_range_fields(): void
    {
        $rules = $this->postRules();

        $missingName = Validator::make([
            'doctor_id'             => $this->doctorId,
            'effective_from'        => '2026-01-01',
            'slot_duration_minutes' => 30,
            'max_patients_per_slot' => 2,
        ], $rules);
        $this->assertTrue($missingName->fails());
        $this->assertArrayHasKey('name', $missingName->errors()->toArray());

        $badRanges = Validator::make([
            'doctor_id'             => $this->doctorId,
            'name'                  => 'X',
            'effective_from'        => '2026-01-01',
            'slot_duration_minutes' => 3,   // below min 5
            'max_patients_per_slot' => 0,   // below min 1
        ], $rules);
        $this->assertTrue($badRanges->fails());
        $this->assertArrayHasKey('slot_duration_minutes', $badRanges->errors()->toArray());
        $this->assertArrayHasKey('max_patients_per_slot', $badRanges->errors()->toArray());
    }

    /* ---------- controller actions (direct invocation) ---------- */

    private function controllerWith(Request $request): DoctorScheduleController
    {
        // Bind the request so the DI-constructed validator sees the right
        // HTTP method, then resolve the controller.
        $this->app->instance('request', $request);
        return $this->app->make(DoctorScheduleController::class);
    }

    public function test_store_persists_schedule_with_name_and_nested_slots(): void
    {
        $payload = [
            'doctor_id'             => $this->doctorId,
            'department_id'         => $this->departmentId,
            'name'                  => 'Store Flow Schedule',
            'effective_from'        => '2026-01-01',
            'slot_duration_minutes' => 30,
            'max_patients_per_slot' => 1,
            'slots'                 => [
                ['day_of_week' => $this->dow, 'start_time' => '09:00', 'end_time' => '12:00'],
            ],
        ];
        $request = Request::create('/api/doctor-schedule', 'POST', $payload);
        $controller = $this->controllerWith($request);

        $controller->store($request);

        $schedule = DoctorSchedule::where('name', 'Store Flow Schedule')->first();
        $this->assertNotNull($schedule, 'Schedule should be created with its name populated.');
        $this->assertEquals(1, $schedule->slots()->count());
        $this->assertEquals($schedule->id, $schedule->slots()->first()->doctor_schedule_id);
    }

    public function test_generate_slots_endpoint_materializes_over_range(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);

        $request = Request::create('/api/doctor-schedule/' . $schedule->id . '/generate-slots', 'POST', [
            'from_date' => $this->date,
            'to_date'   => $this->date,
        ]);
        $controller = $this->controllerWith($request);

        $response = $controller->generateSlots($request, $schedule->id);
        $data = $response->getData(true);

        $this->assertEquals(6, $data['data']['slots_generated'] ?? $data['slots_generated'] ?? null);
        $this->assertEquals(6, AppointmentSlot::where('doctor_id', $this->doctorId)
            ->whereDate('slot_date', $this->date)->count());
    }

    /* ---------- doctor list (users with Doctor role) ---------- */

    public function test_get_doctors_returns_only_doctor_role_users(): void
    {
        $doctorRoleId = (int) \App\Models\Role::query()->where('name', 'Doctor')->value('id');
        $this->assertNotEmpty($doctorRoleId, 'A Doctor role must exist.');

        $doctors = (new UserRepository())->getDoctors();
        $this->assertNotEmpty($doctors, 'Seeded doctor users should be returned.');
        foreach ($doctors as $doc) {
            $roleIds = array_map('strval', (array) User::find($doc->id)->role_ids);
            $this->assertContains((string) $doctorRoleId, $roleIds);
        }
    }

    public function test_available_slots_endpoint_returns_open_slots(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeSlot($schedule);

        $request = Request::create('/api/doctor-schedule/available-slots', 'GET', [
            'doctor_id' => $this->doctorId,
            'date'      => $this->date,
        ]);
        $controller = $this->controllerWith($request);

        $response = $controller->availableSlots($request);
        $data = $response->getData(true);
        $slots = $data['data'] ?? $data;

        $this->assertCount(6, $slots);
    }
}
