<?php

namespace App\Services\Ipd;

use App\Enums\IpdAdmissionStatusEnum;
use App\Exceptions\ApiException;
use App\Models\IpdAdmission;
use App\Models\IpdDeathCertificate;
use App\Repositories\IpdDeathCertificateRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpdDeathCertificateService
{
    protected IpdDeathCertificateRepository $repository;

    public function __construct(IpdDeathCertificateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(int $admissionId, array $data): IpdDeathCertificate
    {
        return DB::transaction(function () use ($admissionId, $data) {
            $admission = IpdAdmission::query()->findOrFail($admissionId);

            if ($admission->admission_status !== IpdAdmissionStatusEnum::DECEASED) {
                throw new ApiException('A death certificate can only be created for a deceased admission.', 422);
            }

            $existing = $this->repository->forAdmission($admissionId);
            if ($existing) {
                throw new ApiException('A death certificate already exists for this admission.', 422);
            }

            $dateYmd = Carbon::parse($data['date_of_death'])->toDateString();

            return IpdDeathCertificate::query()->create(array_merge($data, [
                'organogram_id'  => $admission->organogram_id,
                'admission_id'   => $admissionId,
                'certificate_no' => $this->generateCertificateNo($dateYmd),
            ]));
        });
    }

    public function update(int $id, array $data): IpdDeathCertificate
    {
        $certificate = $this->repository->show($id);
        $this->assertNotFinalized($certificate);

        $certificate->fill($data);
        $certificate->save();

        return $certificate->fresh();
    }

    public function certify(int $id, int $actorId): IpdDeathCertificate
    {
        $certificate = $this->repository->show($id);
        $this->assertNotFinalized($certificate);

        if (empty($certificate->immediate_cause)) {
            throw new ApiException('Immediate cause of death is required before certifying.', 422);
        }

        $certificate->is_finalized = true;
        $certificate->certified_by = $actorId;
        $certificate->certified_at = now();
        $certificate->save();

        return $certificate->fresh();
    }

    protected function assertNotFinalized(IpdDeathCertificate $certificate): void
    {
        if ($certificate->is_finalized) {
            throw new ApiException("Death certificate {$certificate->certificate_no} is already certified and cannot be edited.", 422);
        }
    }

    protected function generateCertificateNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'DEATH_CERTIFICATE')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'DEATH_CERTIFICATE',
                    'prefix'        => 'DC',
                    'separator'     => '-',
                    'next_sequence' => 2,
                    'sequence_date' => $dateYmd,
                    'status'        => 1,
                    'sort_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')
                    ->where('id', $row->id)
                    ->update([
                        'next_sequence' => $seq + 1,
                        'updated_at'    => now(),
                    ]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "DC-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
