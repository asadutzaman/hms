<?php

namespace App\Http\Resources\Report;

use App\Http\Resources\BaseResource;

class DailyCollectionReportResource extends BaseResource
{
    /**
     * The repository already returns plain stdClass rows shaped with final
     * field names, so just pass them through as-is.
     */
    public function toArray($request)
    {
        return (array) $this->resource;
    }
}
