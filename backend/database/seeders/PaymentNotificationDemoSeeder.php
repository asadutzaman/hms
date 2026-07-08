<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\OpdBill;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Payment gateway reconciliation volume (across initiated/success/failed) and
 * in-app notification volume (read + unread) so the payment transactions
 * page and notification center aren't empty. Both are cross-cutting demo
 * data layered on top of whatever bills/appointments already exist.
 *
 * Idempotent: transactions keyed by `transaction_ref`; notifications are
 * capped at a fixed count per user via an existence check.
 */
class PaymentNotificationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('[PaymentNotificationDemoSeeder] Starting ...');

        $this->seedPaymentTransactions();
        $this->seedNotifications();

        $this->command->info('[PaymentNotificationDemoSeeder] Done.');
    }

    private function seedPaymentTransactions(): void
    {
        $bills = OpdBill::query()->limit(10)->get(['id', 'total', 'opd_visit_id']);
        $appointments = Appointment::query()->limit(6)->get(['id', 'consultation_fee', 'patient_id']);

        $statuses = ['success', 'success', 'success', 'failed', 'initiated'];
        $created = 0;
        $seq = 1;

        foreach ($bills as $bill) {
            $ref = 'PAY-DEMO-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
            if (PaymentTransaction::query()->where('transaction_ref', $ref)->exists()) {
                $seq++;
                continue;
            }

            $patientId = DB::table('opd_visits')->where('id', $bill->opd_visit_id)->value('patient_id');
            $status = $statuses[$seq % count($statuses)];

            PaymentTransaction::query()->forceCreate([
                'transaction_ref' => $ref,
                'payable_type' => OpdBill::class, 'payable_id' => $bill->id,
                'patient_id' => $patientId,
                'amount' => $bill->total ?: 800,
                'currency' => 'BDT', 'gateway' => 'mock_gateway',
                'gateway_reference' => 'MOCK-' . strtoupper(uniqid()),
                'payment_status' => $status,
                'failure_reason' => $status === 'failed' ? 'Card declined by issuing bank (demo)' : null,
                'initiated_at' => Carbon::now()->subDays(rand(0, 10)),
                'completed_at' => $status !== 'initiated' ? Carbon::now()->subDays(rand(0, 9)) : null,
                'status' => 1,
            ]);
            $created++;
            $seq++;
        }

        foreach ($appointments as $appointment) {
            $ref = 'PAY-DEMO-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
            if (PaymentTransaction::query()->where('transaction_ref', $ref)->exists()) {
                $seq++;
                continue;
            }

            $status = $statuses[$seq % count($statuses)];
            PaymentTransaction::query()->forceCreate([
                'transaction_ref' => $ref,
                'payable_type' => Appointment::class, 'payable_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'amount' => $appointment->consultation_fee ?: 800,
                'currency' => 'BDT', 'gateway' => 'mock_gateway',
                'gateway_reference' => 'MOCK-' . strtoupper(uniqid()),
                'payment_status' => $status,
                'failure_reason' => $status === 'failed' ? 'Insufficient balance (demo)' : null,
                'initiated_at' => Carbon::now()->subDays(rand(0, 15)),
                'completed_at' => $status !== 'initiated' ? Carbon::now()->subDays(rand(0, 14)) : null,
                'status' => 1,
            ]);
            $created++;
            $seq++;
        }

        $this->command->info("[PaymentNotificationDemoSeeder] Payment transactions created: {$created}");
    }

    private function seedNotifications(): void
    {
        $userIds = User::query()->limit(8)->pluck('id')->all();
        $templates = [
            ['appointment_reminder', 'Upcoming Appointment', 'You have an appointment scheduled tomorrow at 09:00 AM.'],
            ['lab_result_ready', 'Lab Result Ready', 'Your lab test result is now available for review.'],
            ['bill_generated', 'New Bill Generated', 'A new bill has been generated for your recent visit.'],
            ['payment_success', 'Payment Successful', 'Your payment was processed successfully.'],
            ['leave_approved', 'Leave Request Approved', 'Your leave request has been approved.'],
        ];

        $created = 0;
        foreach ($userIds as $userId) {
            $existingCount = DB::table('notifications')->where('user_id', $userId)->count();
            if ($existingCount >= 5) {
                continue;
            }

            foreach ($templates as $i => [$type, $title, $body]) {
                $isRead = $i % 2 === 0;
                Notification::query()->forceCreate([
                    'user_id' => $userId, 'channel' => 'in_app', 'type' => $type,
                    'title' => $title, 'body' => $body,
                    'delivery_status' => 'sent', 'sent_at' => Carbon::now()->subDays(rand(0, 10)),
                    'is_read' => $isRead, 'read_at' => $isRead ? Carbon::now()->subDays(rand(0, 5)) : null,
                    'status' => 1,
                ]);
                $created++;
            }
        }

        $this->command->info("[PaymentNotificationDemoSeeder] Notifications created: {$created}");
    }
}
