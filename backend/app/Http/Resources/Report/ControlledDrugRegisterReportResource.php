<?php

namespace App\Http\Resources\Report;

use App\Http\Resources\BaseResource;

class ControlledDrugRegisterReportResource extends BaseResource
{
    /**
     * The repository already shapes this row into a plain array with final
     * field names, so just pass it through as-is.
     */
    public function toArray($request)
    {
        return (array) $this->resource;
    }
}
