<?php

namespace App\Services\Ipd;

use App\Enums\IpdAdmissionActionEnum;
use App\Enums\IpdAdmissionStatusEnum;
use App\Exceptions\ApiException;
use App\Models\IpdAdmission;
use App\Repositories\IpdAdmissionRepository;
use Illuminate\Support\Facades\DB;

class IpdAdmissionService
{
    protected IpdAdmissionRepository $admissionRepository;
    protected IpdBillService $billService;

    protected static array $exitActionMap = [
        IpdAdmissionStatusEnum::DISCHARGED => IpdAdmissionActionEnum::DISCHARGE,
        IpdAdmissionStatusEnum::DAMA       => IpdAdmissionActionEnum::DAMA,
        IpdAdmissionStatusEnum::DECEASED   => IpdAdmissionActionEnum::DECEASED,
    ];

    public function __construct(IpdAdmissionRepository $admissionRepository, IpdBillService $billService)
    {
        $this->admissionRepository = $admissionRepository;
        $this->billService         = $billService;
    }

    /**
     * Exit an admission (discharge / DAMA / deceased). Refreshes room charges
     * up to now() first, then requires the bill to be cleared (balance <= 0 or
     * paid/waived) unless the caller supplies an override reason — in which
     * case the exit proceeds and is tagged as a DISCHARGE_OVERRIDE for audit.
     */
    public function exit(int $admissionId, string $toStatus, int $actorId, ?string $remarks = null, ?string $overrideReason = null): IpdAdmission
    {
        if (!isset(self::$exitActionMap[$toStatus])) {
            throw new ApiException("Invalid exit status '{$toStatus}'.", 422);
        }

        return DB::transaction(function () use ($admissionId, $toStatus, $actorId, $remarks, $overrideReason) {
            $this->billService->refreshRoomCharges($admissionId, $actorId);

            $cleared = $this->billService->isCleared($admissionId);
            $action = self::$exitActionMap[$toStatus];
            $meta = [];

            if (!$cleared) {
                if (empty($overrideReason)) {
                    $bill = $this->billService->findForAdmission($admissionId);
                    throw new ApiException(
                        "Cannot discharge — outstanding bill balance of {$bill->balance}. Provide an override reason to discharge anyway.",
                        422,
                    );
                }

                $action = IpdAdmissionActionEnum::DISCHARGE_OVERRIDE;
                $meta['discharge_override_reason'] = $overrideReason;
            }

            return $this->admissionRepository->transitionStatus(
                $admissionId,
                $toStatus,
                $actorId,
                $remarks,
                $meta,
                $action,
            );
        });
    }
}
