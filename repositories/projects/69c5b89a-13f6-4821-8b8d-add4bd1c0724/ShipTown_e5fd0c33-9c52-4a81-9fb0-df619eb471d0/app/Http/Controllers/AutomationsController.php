<?php

namespace App\Http\Controllers;

use App\Helpers\CsvStreamedResponse;
use App\Modules\Reports\src\Models\AutomationReport;
use Illuminate\Http\Request;

class AutomationsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('filename')) {
            $report = new AutomationReport();

            return CsvStreamedResponse::fromQueryBuilder($report->queryBuilder(), $request->get('filename'));
        }

        return view('settings.automations');
    }
}
