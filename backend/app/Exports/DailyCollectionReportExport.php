<?php

namespace App\Exports;

use App\Http\Resources\Report\DailyCollectionReportResource;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Repositories\Report\DailyCollectionReportRepository;
use App\Services\ResourceService;

class DailyCollectionReportExport implements FromView
{
    protected $repository;

    public function view(): View
    {
        $repository = (new DailyCollectionReportRepository())->init();
        $result = $repository->getDailyCollectionExport();
        $resultResource = ResourceService::getResourceCollection($result['results'], DailyCollectionReportResource::class);

        $response = [
            'results' => $resultResource,
        ];
        return view('reports.hms.dailyCollection.dailyCollectionList', $response);
    }
}
