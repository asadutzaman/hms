<?php

namespace App\Http\Resources;

class NotificationResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            return [
                'id'              => $this->id,
                'uuid'            => $this->uuid,
                'channel'         => $this->channel,
                'type'            => $this->type,
                'title'           => $this->title,
                'body'            => $this->body,
                'data'            => $this->data,
                'delivery_status' => $this->delivery_status,
                'sent_at'         => $this->sent_at,
                'is_read'         => (bool) $this->is_read,
                'read_at'         => $this->read_at,
                'created_at'      => $this->created_at,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
