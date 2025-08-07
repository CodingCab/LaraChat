<?php

namespace App\Http\Controllers;

use App\Helpers\CsvStreamedResponse;
use App\Modules\Reports\src\Models\DataCollectionReport;
use Illuminate\Http\Request;

class DataCollectorController extends Controller
{
    public function index(Request $request, $id)
    {
        if ($request->has('filename')) {
            $report = new DataCollectionReport();

            return CsvStreamedResponse::fromQueryBuilder($report->queryBuilder(), $request->get('filename'));
        }

        return view('data-collector-page', ['data_collection_id' => $id]);
    }
}
