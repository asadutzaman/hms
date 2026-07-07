<?php

namespace App\Services\Notification;

interface SmsGatewayInterface
{
    /**
     * @return bool true if the gateway accepted the message for delivery.
     */
    public function send(string $to, string $message): bool;
}
