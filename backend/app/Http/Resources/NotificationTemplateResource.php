<?php

namespace App\Http\Resources;

class NotificationTemplateResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            return [
                'id'               => $this->id,
                'uuid'             => $this->uuid,
                'key'              => $this->key,
                'name'             => $this->name,
                'channel'          => $this->channel,
                'subject_template' => $this->subject_template,
                'body_template'    => $this->body_template,
                'is_active'        => (bool) $this->is_active,
                'created_by_name'  => $baseData['created_by_name'] ?? null,
                'updated_by_name'  => $baseData['updated_by_name'] ?? null,
                'created_at'       => $baseData['created_at'] ?? null,
                'updated_at'       => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
