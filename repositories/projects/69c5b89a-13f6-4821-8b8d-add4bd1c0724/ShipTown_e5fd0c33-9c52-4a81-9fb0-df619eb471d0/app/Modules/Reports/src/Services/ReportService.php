<?php

namespace App\Modules\Reports\src\Services;

use App\Modules\Reports\src\Models\Report;

class ReportService
{
    public static function fromQuery($query): Report
    {
        $report = new Report;

        $report->baseQuery = $query;

        return $report;
    }
}
