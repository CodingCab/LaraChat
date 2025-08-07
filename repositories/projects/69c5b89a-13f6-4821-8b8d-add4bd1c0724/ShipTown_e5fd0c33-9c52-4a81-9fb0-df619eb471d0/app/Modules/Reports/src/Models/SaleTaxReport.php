<?php

namespace App\Modules\Reports\src\Models;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use Spatie\QueryBuilder\AllowedFilter;

class SaleTaxReport extends Report
{
    public function __construct()
    {
        parent::__construct();

        $this->report_name = 'Sale Tax Report';
        $this->defaultSort = 'rate';

        $this->baseQuery = SaleTax::query();

        $this->fields = [
            'id' => 'id',
            'code' => 'code',
            'rate' => 'rate',
        ];

        $this->casts = [
            'rate' => 'float',
        ];

        $this->addFilter(
            AllowedFilter::callback('search', function ($query, $value) {
                $query->whereHas('code', function ($query) use ($value) {
                    $query->where(['code' => $value]);
                });
            })
        );
    }
}
