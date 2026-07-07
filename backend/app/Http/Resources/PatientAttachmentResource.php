<?php

namespace App\Http\Resources;

class PatientAttachmentResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $data = [
                'id'              => $this->id,
                'uuid'            => $this->uuid,
                'patient_id'      => $this->patient_id,
                'file_id'         => $this->file_id,
                'category'        => $this->category,
                'title'           => $this->title,
                'description'     => $this->description,
                'attachable_type' => $this->attachable_type,
                'attachable_id'   => $this->attachable_id,
                'uploaded_by'     => $this->uploaded_by,
                'uploaded_at'     => $this->uploaded_at,
                'created_by_name' => $baseData['created_by_name'] ?? null,
            ];

            if ($resource->relationLoaded('file') && $resource->file) {
                $data['file'] = [
                    'file_id'           => $resource->file->file_id,
                    'original_filename' => $resource->file->original_filename,
                    'file_url'          => $resource->file->file_url,
                    'mime_type'         => $resource->file->mime_type,
                    'ext'               => $resource->file->ext,
                    'size'              => $resource->file->size,
                ];
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
