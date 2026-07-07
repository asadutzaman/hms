<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Patient;
use App\Models\PatientOtpCode;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * F-17-01 Patient Login & Profile — email-based OTP login (per explicit
 * product direction; the backlog's acceptance criteria mentions "OTP login
 * via mobile" but SMS has no real gateway configured — see
 * project_hms_sprint8_scope memory — email is the actual delivery channel).
 */
class PatientAuthService
{
    protected const OTP_TTL_MINUTES = 10;
    protected const OTP_RESEND_COOLDOWN_SECONDS = 60;
    protected const MAX_VERIFY_ATTEMPTS = 5;

    /**
     * Always returns void/no signal either way — deliberately doesn't
     * reveal whether the email belongs to a registered patient (basic
     * account-enumeration hygiene). The controller returns the same
     * generic "check your email" message regardless.
     */
    public function requestOtp(string $email): void
    {
        $patient = Patient::query()->where('email', $email)->first();
        if (!$patient) {
            return;
        }

        $recentUnconsumed = PatientOtpCode::query()
            ->where('email', $email)
            ->where('purpose', 'login')
            ->whereNull('consumed_at')
            ->where('created_at', '>=', now()->subSeconds(self::OTP_RESEND_COOLDOWN_SECONDS))
            ->exists();
        if ($recentUnconsumed) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        PatientOtpCode::query()->create([
            'patient_id' => $patient->id,
            'email'      => $email,
            'code_hash'  => hash('sha256', $code),
            'purpose'    => 'login',
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        app(NotificationService::class)->sendEventToContact(
            'patient_login_otp',
            ['email' => $email],
            [
                'patient_name' => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                'otp_code'     => $code,
                'expires_in'   => self::OTP_TTL_MINUTES,
            ],
            ['email']
        );
    }

    public function verifyOtp(string $email, string $code): Patient
    {
        // NOTE: a wrong-code attempt must NOT throw from inside this
        // transaction — DB::transaction() rolls back the whole closure on
        // any exception, which would silently discard the attempt_count
        // increment below and defeat the brute-force lockout entirely.
        // Instead, the closure returns null on a wrong code (letting the
        // transaction commit normally) and the throw happens after, outside
        // the transaction.
        $otp = DB::transaction(function () use ($email, $code) {
            $otp = PatientOtpCode::query()
                ->where('email', $email)
                ->where('purpose', 'login')
                ->whereNull('consumed_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$otp) {
                throw new ApiException('No pending OTP request for this email. Please request a new code.', 422);
            }

            if ($otp->expires_at->isPast()) {
                throw new ApiException('This OTP has expired. Please request a new code.', 422);
            }

            if ($otp->attempt_count >= self::MAX_VERIFY_ATTEMPTS) {
                throw new ApiException('Too many incorrect attempts. Please request a new code.', 422);
            }

            if (!hash_equals($otp->code_hash, hash('sha256', $code))) {
                $otp->increment('attempt_count');
                return null;
            }

            $otp->consumed_at = now();
            $otp->save();

            return $otp;
        });

        if ($otp === null) {
            throw new ApiException('Incorrect OTP code.', 422);
        }

        $patient = Patient::query()->find($otp->patient_id);
        if (!$patient) {
            throw new ApiException('Patient record not found.', 404);
        }

        return $patient;
    }
}
