<?php

namespace App\Services\Hr;

use App\Exceptions\ApiException;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * F-13-02 Attendance Management. "Biometric punches synced" — this project
 * has no real biometric device/hardware integration; syncPunches() is the
 * integration point a biometric device push (or its middleware) would call,
 * accepting punches in a device-agnostic {employee_id, punch_time,
 * direction} shape and recording them with source='biometric'. Manual
 * check-in/check-out (source='manual') covers staff self-service or admin
 * correction.
 */
class AttendanceService
{
    public function checkIn(int $employeeId, ?int $shiftId, string $source, int $actorId): AttendanceRecord
    {
        return DB::transaction(function () use ($employeeId, $shiftId, $source, $actorId) {
            $today = now()->toDateString();
            $record = AttendanceRecord::query()
                ->where('employee_id', $employeeId)
                ->where('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if ($record && $record->check_in_time) {
                throw new ApiException('Already checked in today.', 422);
            }

            if ($record) {
                $record->check_in_time = now();
                $record->shift_id = $shiftId ?? $record->shift_id;
                $record->source = $source;
                $record->recorded_by = $actorId;
                $record->save();
                return $record->fresh();
            }

            return AttendanceRecord::query()->create([
                'employee_id'      => $employeeId,
                'attendance_date'  => $today,
                'shift_id'         => $shiftId,
                'check_in_time'    => now(),
                'source'           => $source,
                'recorded_by'      => $actorId,
                'created_by'       => $actorId,
            ]);
        });
    }

    public function checkOut(int $employeeId, string $source, int $actorId): AttendanceRecord
    {
        return DB::transaction(function () use ($employeeId, $source, $actorId) {
            $today = now()->toDateString();
            $record = AttendanceRecord::query()
                ->where('employee_id', $employeeId)
                ->where('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if (!$record || !$record->check_in_time) {
                throw new ApiException('No check-in found for today.', 422);
            }
            if ($record->check_out_time) {
                throw new ApiException('Already checked out today.', 422);
            }

            $checkOutTime = now();
            $record->check_out_time = $checkOutTime;
            $record->work_hours = round(Carbon::parse($record->check_in_time)->floatDiffInHours($checkOutTime), 2);
            $record->source = $source;
            $record->recorded_by = $actorId;
            $record->save();

            return $record->fresh();
        });
    }

    /**
     * Bulk sync entry point for punches pushed from an external
     * source (biometric device / middleware). Each punch is
     * {employee_id, punch_time (Y-m-d H:i:s), direction: 'in'|'out'}.
     */
    public function syncPunches(array $punches, int $actorId): array
    {
        $results = [];
        foreach ($punches as $punch) {
            try {
                $punchTime = Carbon::parse($punch['punch_time']);
                $date = $punchTime->toDateString();
                $employeeId = (int) $punch['employee_id'];

                $record = AttendanceRecord::query()
                    ->where('employee_id', $employeeId)
                    ->where('attendance_date', $date)
                    ->first();

                if (!$record) {
                    $record = AttendanceRecord::query()->create([
                        'employee_id'     => $employeeId,
                        'attendance_date' => $date,
                        'source'          => 'biometric',
                        'created_by'      => $actorId,
                    ]);
                }

                if (($punch['direction'] ?? 'in') === 'in') {
                    if (!$record->check_in_time || $punchTime->lt($record->check_in_time)) {
                        $record->check_in_time = $punchTime;
                    }
                } else {
                    if (!$record->check_out_time || $punchTime->gt($record->check_out_time)) {
                        $record->check_out_time = $punchTime;
                    }
                }

                if ($record->check_in_time && $record->check_out_time) {
                    $record->work_hours = round(Carbon::parse($record->check_in_time)->floatDiffInHours($record->check_out_time), 2);
                }

                $record->source = 'biometric';
                $record->save();

                $results[] = ['employee_id' => $employeeId, 'attendance_date' => $date, 'synced' => true];
            } catch (\Throwable $e) {
                $results[] = ['employee_id' => $punch['employee_id'] ?? null, 'synced' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function manualEntry(array $data, int $actorId): AttendanceRecord
    {
        return DB::transaction(function () use ($data, $actorId) {
            $record = AttendanceRecord::query()
                ->where('employee_id', $data['employee_id'])
                ->where('attendance_date', $data['attendance_date'])
                ->first();

            $payload = [
                'employee_id'     => $data['employee_id'],
                'attendance_date' => $data['attendance_date'],
                'shift_id'        => $data['shift_id'] ?? null,
                'check_in_time'   => $data['check_in_time'] ?? null,
                'check_out_time'  => $data['check_out_time'] ?? null,
                'source'          => 'manual',
                'remarks'         => $data['remarks'] ?? null,
                'recorded_by'     => $actorId,
            ];

            if (!empty($payload['check_in_time']) && !empty($payload['check_out_time'])) {
                $payload['work_hours'] = round(Carbon::parse($payload['check_in_time'])->floatDiffInHours($payload['check_out_time']), 2);
            }

            if ($record) {
                $payload['updated_by'] = $actorId;
                $record->fill($payload);
                $record->save();
                return $record->fresh();
            }

            $payload['created_by'] = $actorId;
            return AttendanceRecord::query()->create($payload);
        });
    }
}
