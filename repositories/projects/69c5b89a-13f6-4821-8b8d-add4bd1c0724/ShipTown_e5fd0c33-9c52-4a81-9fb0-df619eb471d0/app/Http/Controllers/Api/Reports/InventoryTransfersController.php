<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\Reports\src\Models\InventoryTransferReport;

class InventoryTransfersController
{
    public function index()
    {
        return InventoryTransferReport::json();
    }
}
