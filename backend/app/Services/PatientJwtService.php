<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientAccessToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Mirrors JwtService's HMAC-signing mechanics (same house style) but is a
 * fully separate stack — own config('patient_jwt.key') secret, own token
 * table (patient_access_tokens), own payload shape (Patient, not User).
 * See project_hms_sprint8_scope memory for why this isn't an extension of
 * the staff JwtService.
 */
class PatientJwtService
{
    protected $token;

    public function getTokenForRequest()
    {
        $request = request();

        if ($this->token !== null) {
            return $this->token;
        }

        $token = $request->query('token');
        if (empty($token)) {
            $token = $request->input('token');
        }
        if (empty($token)) {
            $token = $request->bearerToken();
        }

        return $this->token = $token;
    }

    public function generateToken(Patient $patient): string
    {
        $sub = [
            'type'       => 'patient',
            'id'         => $patient->id,
            'uuid'       => $patient->uuid,
            'mrn'        => $patient->mrn,
            'full_name'  => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
            'email'      => $patient->email,
        ];

        $expMinutes = config('patient_jwt.exp.access');
        $exp = time() + ($expMinutes * 60);
        $expDateTime = Carbon::createFromTimestamp($exp)->toDateTimeString();

        $token = $this->createToken($sub, $exp);

        PatientAccessToken::query()->create([
            'patient_id'   => $patient->id,
            'access_token' => $token,
            'expires_at'   => $expDateTime,
        ]);

        return $token;
    }

    public function createToken(array $sub, int $exp): string
    {
        $header = $this->encode(config('patient_jwt.header'));
        $payload = $this->encode([
            'sub' => $sub,
            'iat' => time(),
            'exp' => $exp,
            'jti' => Str::random(10),
        ]);
        $signature = $this->sign($header, $payload);

        return "$header.$payload.$signature";
    }

    protected function encode($data)
    {
        return $this->base64UrlEncode(json_encode($data));
    }

    protected function decode($data)
    {
        return json_decode($this->base64UrlDecode($data));
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    protected function sign($header, $payload)
    {
        return $this->base64UrlEncode(hash_hmac('SHA256', "$header.$payload", config('patient_jwt.key'), true));
    }

    public function verify($token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signatureProvided] = $parts;
        $signatureExpected = $this->base64UrlEncode(hash_hmac('SHA256', "$header.$payload", config('patient_jwt.key'), true));

        return hash_equals($signatureExpected, $signatureProvided);
    }

    public function getPayload($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        return $this->decode($parts[1]);
    }

    public function isExpired($token): bool
    {
        $payload = $this->getPayload($token);
        if (empty($payload) || empty($payload->exp)) {
            return true;
        }

        return Carbon::createFromTimestamp($payload->exp)->isPast();
    }

    /**
     * Full verify chain used by PatientAuthVerifyMiddleware — signature,
     * expiry, and a live (unrevoked, unexpired) DB row must all agree,
     * same defense-in-depth convention as JwtService::getTokeInfo().
     */
    public function getTokenInfo()
    {
        $token = $this->getTokenForRequest();
        if (empty($token) || !$this->verify($token) || $this->isExpired($token)) {
            return null;
        }

        $payload = $this->getPayload($token);
        if (empty($payload) || empty($payload->sub)) {
            return null;
        }

        $liveToken = PatientAccessToken::query()
            ->where('access_token', $token)
            ->where('revoked', false)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        // Opportunistically clean up expired rows, same convention as the staff stack.
        PatientAccessToken::query()->where('expires_at', '<', Carbon::now())->delete();

        if (empty($liveToken)) {
            return null;
        }

        return $payload->sub;
    }

    public function revokeToken(string $token): void
    {
        PatientAccessToken::query()->where('access_token', $token)->update(['revoked' => true]);
    }
}
