<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\NotificationTemplateRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected NotificationTemplateRepository $templateRepository;

    public function __construct(NotificationTemplateRepository $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * Send a logical event to a user across whichever requested channels
     * have a configured, active template. Templates are looked up as
     * "{eventKey}.{channel}" (e.g. "critical_lab_value.in_app",
     * "critical_lab_value.email") — a channel with no matching template is
     * silently skipped rather than failing the whole send, so a deployment
     * can enable channels incrementally by adding templates.
     */
    public function sendEvent(string $eventKey, int $userId, array $data, array $channels): array
    {
        $sent = [];
        foreach ($channels as $channel) {
            $template = $this->templateRepository->findByKey("{$eventKey}.{$channel}");
            if (!$template) {
                continue;
            }
            $sent[] = $this->dispatch($userId, $channel, $template->key, $template->subject_template, $template->body_template, $data);
        }
        return $sent;
    }

    /** Send using an explicit body/subject rather than a stored template — for ad-hoc notifications. */
    public function sendRaw(int $userId, string $channel, string $type, ?string $subject, string $body, array $data = []): Notification
    {
        return $this->dispatch($userId, $channel, $type, $subject, $body, $data);
    }

    /**
     * Send templated notifications directly to a contact address (email or
     * phone) rather than a User record — needed for patient-facing
     * notifications (e.g. appointment reminders), since Patient is a
     * separate table from User and has no portal/login yet. Skips the
     * `notifications` table entirely (that table is the staff-facing
     * in-app notification center; there is nothing to show a patient who
     * can't log in), and silently skips channels with no matching template
     * or no contact address, same convention as sendEvent().
     *
     * @param array{email?: ?string, phone?: ?string} $contact
     * @return array<int, bool> keyed by channel => whether delivery succeeded
     */
    public function sendEventToContact(string $eventKey, array $contact, array $data, array $channels): array
    {
        $results = [];
        foreach ($channels as $channel) {
            $template = $this->templateRepository->findByKey("{$eventKey}.{$channel}");
            if (!$template) {
                continue;
            }

            $subject = $template->subject_template ? $this->render($template->subject_template, $data) : null;
            $body = $this->render($template->body_template, $data);

            try {
                if ($channel === 'email') {
                    if (empty($contact['email'])) {
                        continue;
                    }
                    Mail::raw($body, function ($message) use ($contact, $subject) {
                        $message->to($contact['email'])->subject($subject ?? '');
                    });
                } elseif ($channel === 'sms') {
                    if (empty($contact['phone'])) {
                        continue;
                    }
                    if (!app(SmsGatewayInterface::class)->send($contact['phone'], $body)) {
                        throw new \RuntimeException('SMS gateway rejected the message.');
                    }
                } else {
                    continue;
                }
                $results[$channel] = true;
            } catch (\Throwable $e) {
                Log::warning("Contact notification delivery failed (channel={$channel}, event={$eventKey}): " . $e->getMessage());
                $results[$channel] = false;
            }
        }

        return $results;
    }

    protected function dispatch(int $userId, string $channel, string $type, ?string $subjectTemplate, string $bodyTemplate, array $data): Notification
    {
        $subject = $subjectTemplate ? $this->render($subjectTemplate, $data) : null;
        $body = $this->render($bodyTemplate, $data);

        $notification = Notification::query()->create([
            'user_id'         => $userId,
            'channel'         => $channel,
            'type'            => $type,
            'title'           => $subject ?? $type,
            'body'            => $body,
            'data'            => $data,
            'delivery_status' => 'pending',
        ]);

        try {
            switch ($channel) {
                case 'in_app':
                    // Persisting the row above is the delivery — nothing else to do.
                    break;
                case 'email':
                    $this->deliverEmail($userId, $subject ?? $type, $body);
                    break;
                case 'sms':
                    $this->deliverSms($userId, $body);
                    break;
            }

            $notification->delivery_status = 'sent';
            $notification->sent_at = now();
            $notification->save();
        } catch (\Throwable $e) {
            $notification->delivery_status = 'failed';
            $notification->failure_reason = $e->getMessage();
            $notification->save();
            Log::warning("Notification delivery failed (channel={$channel}, user={$userId}): " . $e->getMessage());
        }

        return $notification->fresh();
    }

    protected function deliverEmail(int $userId, string $subject, string $body): void
    {
        $user = User::query()->find($userId);
        if (!$user || !$user->email) {
            throw new \RuntimeException('User has no email address on file.');
        }
        Mail::raw($body, function ($message) use ($user, $subject) {
            $message->to($user->email)->subject($subject);
        });
    }

    protected function deliverSms(int $userId, string $body): void
    {
        $user = User::query()->find($userId);
        if (!$user || !$user->phone) {
            throw new \RuntimeException('User has no phone number on file.');
        }
        $gateway = app(SmsGatewayInterface::class);
        if (!$gateway->send($user->phone, $body)) {
            throw new \RuntimeException('SMS gateway rejected the message.');
        }
    }

    protected function render(string $template, array $data): string
    {
        $search = array_map(fn ($k) => '{{' . $k . '}}', array_keys($data));
        $replace = array_values($data);
        return str_replace($search, $replace, $template);
    }
}
