<?php

namespace App\Exports;

use App\Http\Resources\Report\DrugExpiryReportResource;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Repositories\Report\DrugExpiryReportRepository;
use App\Services\ResourceService;

class DrugExpiryReportExport implements FromView
{
    protected $repository;

    public function view(): View
    {
        $repository = (new DrugExpiryReportRepository())->init();
        $result = $repository->getExpiryListExport();
        $resultResource = ResourceService::getResourceCollection($result['results'], DrugExpiryReportResource::class);

        $response = [
            'results' => $resultResource,
        ];
        return view('reports.pharmacy.drugExpiry.drugExpiryList', $response);
    }
}
