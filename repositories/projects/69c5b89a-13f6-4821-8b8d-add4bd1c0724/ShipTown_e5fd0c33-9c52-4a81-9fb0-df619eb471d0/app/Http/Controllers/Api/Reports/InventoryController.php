<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\Reports\src\Models\InventoryReport;

class InventoryController
{
    public function index()
    {
        return InventoryReport::json();
    }
}
