<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Pick;
use Spatie\QueryBuilder\AllowedFilter;

class PicksReport extends Report
{
    public string $report_name = 'Picks';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->baseQuery = Pick::query()
            ->leftJoin('users as user', 'picks.user_id', '=', 'user.id')
            ->leftJoin('products as product', 'picks.product_id', '=', 'product.id');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->addField('Picked At', 'picks.created_at', 'datetime', hidden: false);
        $this->addField('Warehouse Code', 'picks.warehouse_code');
        $this->addField('Product SKU', 'product.sku', hidden: false);
        $this->addField('Product Name', 'product.name', hidden: false);
        $this->addField('Picked', 'picks.quantity_picked', 'numeric', hidden: false);
        $this->addField('Skipped', 'picks.quantity_skipped_picking', 'numeric', hidden: false);
        $this->addField('Picker', 'user.name', hidden: false);
    }
}
