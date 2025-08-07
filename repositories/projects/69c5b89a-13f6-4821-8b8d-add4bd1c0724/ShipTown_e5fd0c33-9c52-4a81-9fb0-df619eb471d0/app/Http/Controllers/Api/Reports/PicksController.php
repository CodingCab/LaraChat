<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\Reports\src\Models\PicksReport;

class PicksController
{
    public function index()
    {
        return PicksReport::json();
    }
}
