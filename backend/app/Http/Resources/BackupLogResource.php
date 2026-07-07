<?php

namespace App\Http\Resources;

class BackupLogResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            return [
                'id'                 => $this->id,
                'uuid'               => $this->uuid,
                'filename'           => $this->filename,
                'size_bytes'         => $this->size_bytes,
                'backup_status'      => $this->backup_status,
                'failure_reason'     => $this->failure_reason,
                'triggered_by_type'  => $this->triggered_by_type,
                'started_at'         => $this->started_at,
                'completed_at'       => $this->completed_at,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
