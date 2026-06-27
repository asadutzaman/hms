<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\OpdVisitAuditLog;
use App\Repositories\OpdVisitAuditLogRepository;
use App\Validators\OpdVisitAuditLogValidator;
use Illuminate\Support\Facades\Validator;

class OpdVisitAuditService
{
    protected $repo;

    public function __construct(OpdVisitAuditLogRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Append an audit log entry. The repository's logAudit() is the canonical
     * entry point used by other services — this method exposes it via the
     * service layer for direct API access (admin/replay tools).
     */
    public function record(array $data, int $actorId): OpdVisitAuditLog
    {
        $data['actor_id']   = $data['actor_id']   ?? $actorId;
        $data['actor_type'] = $data['actor_type'] ?? 'user';
        $data['occurred_at']= $data['occurred_at']?? now();

        $rules = (new OpdVisitAuditLogValidator())->rules();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ApiException(
                'Validation failed: ' . implode('; ', $validator->errors()->all()),
                422,
                $validator->errors()->toArray(),
            );
        }

        return $this->repo->create(array_merge($data, [
            'ip_address' => $data['ip_address'] ?? request()?->ip(),
            'user_agent' => $data['user_agent'] ?? request()?->userAgent(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]));
    }

    public function listForVisit(int $visitId)
    {
        return $this->repo->newQuery()
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('occurred_at')
            ->get();
    }

    public function find(int $id): ?OpdVisitAuditLog
    {
        return $this->repo->find($id);
    }
}