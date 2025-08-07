<?php

namespace App\Modules\Reports\src\Models;

use App\Models\InventoryReservation;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

class InventoryReservationReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Reservations';
        $this->defaultSort = '-inventory_reservations.id';

        $this->baseQuery = InventoryReservation::query()
            ->leftJoin('products as product', 'inventory_reservations.product_sku', '=', 'product.sku')
            ->leftJoin('warehouses as warehouse', 'inventory_reservations.warehouse_code', '=', 'warehouse.code');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );
        $this->addField('Created At', 'inventory_reservations.created_at', 'datetime', hidden: false);
        $this->addField('Product SKU', 'product.sku', hidden: false);
        $this->addField('Product Name', 'product.name', hidden: false);
        $this->addField('Warehouse Code', 'warehouse.code', hidden: false);
        $this->addField('Warehouse Name', 'warehouse.name');
        $this->addField('Quantity Reserved', 'inventory_reservations.quantity_reserved', 'numeric', hidden: false);
        $this->addField('Comment', 'inventory_reservations.comment', hidden: false);
        $this->addField('Inventory ID', 'inventory_reservations.inventory_id', 'numeric');
        $this->addField('id', 'inventory_reservations.id', 'numeric');
        $this->addField('Custom UUID', 'inventory_reservations.custom_uuid');

        $this->addAllowedInclude(AllowedInclude::relationship('causer'));
    }
}
