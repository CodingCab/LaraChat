<?php

namespace App\Modules\StocktakeSuggestions\src\Reports;

use App\Models\Warehouse;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class StocktakeSuggestionsTotalsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Stocktakes Suggestions';

        $this->baseQuery = Warehouse::query();

        $this->defaultSort = 'warehouse_code';

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('warehouses.code', "$value%");
            })
        );

        $this->addField('warehouse_code', 'warehouses.code', hidden: false);
        $this->addField('count', DB::raw("SELECT count(DISTINCT inventory_id) FROM stocktake_suggestions WHERE stocktake_suggestions.warehouse_id = warehouses.id"), type: 'numeric', hidden: false);

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
