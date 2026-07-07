<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Log;

/**
 * Default SMS gateway — no SMS provider (Twilio/Nexmo/etc.) is configured
 * anywhere in this app yet, so this simply logs the message and reports
 * success, the same way Laravel's "log" mail driver works for local dev.
 * Swap the `SmsGatewayInterface` binding in a service provider for a real
 * gateway once one is procured — nothing else in the Notification Center
 * needs to change.
 */
class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $to, string $message): bool
    {
        Log::channel(config('logging.default'))->info("[SMS STUB] To: {$to} | Message: {$message}");
        return true;
    }
}
