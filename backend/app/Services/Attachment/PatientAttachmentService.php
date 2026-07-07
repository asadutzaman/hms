<?php

namespace App\Services\Attachment;

use App\Models\File;
use App\Models\PatientAttachment;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PatientAttachmentService
{
    /**
     * Store the uploaded file via the existing generic upload pipeline
     * (StorageService — same convention as FileController::upload()) and
     * link it to the patient. The generic `files` table has no linkage to
     * domain records, so `PatientAttachment` is the polymorphic join.
     */
    public function upload(UploadedFile $file, array $data, int $actorId): PatientAttachment
    {
        return DB::transaction(function () use ($file, $data, $actorId) {
            $storageService = new StorageService();

            $originalFilename = $file->getClientOriginalName();
            $fileName = time() . '_' . str_replace(' ', '', $originalFilename);

            // Must read these before StorageService::upload() moves the file off disk.
            $ext = $file->extension();
            $fileType = $file->getClientOriginalExtension();
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();

            $componentName = 'ClinicalAttachment';
            $fileDir = $storageService->getUploadDirectory($originalFilename, $componentName);
            $filePath = 'uploads/' . $fileDir . '/' . $fileName;

            $uploadDir = 'app/public/uploads/' . $fileDir;
            $path = storage_path($uploadDir);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $storageService->upload($file, $fileName, $path);

            $fileRow = File::query()->create([
                'file_id'           => uniqid('', true),
                'filename'          => $fileName,
                'original_filename' => $originalFilename,
                'file_type'         => $fileType,
                'mime_type'         => $mimeType,
                'file_path'         => $filePath,
                'file_url'          => $filePath,
                'ext'               => $ext,
                'size'              => $size,
                'owner_id'          => $actorId,
            ]);

            return PatientAttachment::query()->create([
                'patient_id'      => $data['patient_id'],
                'file_id'         => $fileRow->file_id,
                'category'        => $data['category'] ?? 'other',
                'title'           => $data['title'] ?? $originalFilename,
                'description'     => $data['description'] ?? null,
                'attachable_type' => $data['attachable_type'] ?? null,
                'attachable_id'   => $data['attachable_id'] ?? null,
                'uploaded_by'     => $actorId,
                'uploaded_at'     => now(),
            ])->load('file');
        });
    }
}
