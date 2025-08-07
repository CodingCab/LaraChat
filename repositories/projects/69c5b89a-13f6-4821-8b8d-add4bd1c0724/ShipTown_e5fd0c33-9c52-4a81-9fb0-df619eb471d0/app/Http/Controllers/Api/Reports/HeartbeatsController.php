<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\Reports\src\Models\HeartbeatsReport;

class HeartbeatsController
{
    public function index()
    {
        return HeartbeatsReport::json();
    }
}
