<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\AssemblyProducts\src\Reports\AssemblyProductsElementsReport;

class AssemblyProductsElementsController
{
    public function index()
    {
        return AssemblyProductsElementsReport::json();
    }
}
