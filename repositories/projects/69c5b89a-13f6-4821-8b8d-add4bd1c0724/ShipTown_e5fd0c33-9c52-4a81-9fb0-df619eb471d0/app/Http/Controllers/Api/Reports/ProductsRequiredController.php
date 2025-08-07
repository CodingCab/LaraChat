<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\Reports\src\Models\ProductsRequiredReport;

class ProductsRequiredController
{
    public function index()
    {
        return ProductsRequiredReport::json();
    }
}
