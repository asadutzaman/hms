<?php

namespace App\Http\Resources;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Repositories\UserRepository;

class OpdVisitAuditLogResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $action = $resource->action ?? null;
            $fromStatus = $resource->from_status ?? null;
            $toStatus = $resource->to_status ?? null;

            $actorName = '';
            if (!empty($resource->actor_id)) {
                $user = (new UserRepository())->getById($resource->actor_id);
                $actorName = $user->name ?? '';
            }

            // `meta` is cast to array on the model; preserve as array
            $meta = $resource->meta;
            if (is_string($meta)) {
                $decoded = json_decode($meta, true);
                $meta = is_array($decoded) ? $decoded : [];
            }
            if ($meta === null) {
                $meta = [];
            }

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'visit_id'       => $this->visit_id,
                'action'         => $action,
                'action_label'   => $action !== null ? OpdVisitActionEnum::label($action) : null,
                'from_status'    => $fromStatus,
                'from_status_label' => $fromStatus !== null ? OpdVisitStatusEnum::label($fromStatus) : null,
                'to_status'      => $toStatus,
                'to_status_label' => $toStatus !== null ? OpdVisitStatusEnum::label($toStatus) : null,
                'actor_id'       => $this->actor_id,
                'actor_name'     => $actorName,
                'remarks'        => $this->remarks,
                'meta'           => $meta,
                'occurred_at'    => $this->occurred_at,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
