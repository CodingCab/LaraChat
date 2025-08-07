<?php

namespace App\Modules\Reports\src\Models;

use App\Models\OrderProduct;
use Spatie\QueryBuilder\AllowedFilter;

class PacklistReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Packlist';

        $this->baseQuery = OrderProduct::query()
            ->leftJoin('orders as order', 'orders_products.order_id', '=', 'order.id')
            ->leftJoin('inventory as inventory_source', function ($join) {
                $join->on('inventory_source.product_id', '=', 'orders_products.product_id');
            })
            ->leftJoin('products', 'products.id', '=', 'orders_products.product_id')
            ->where('quantity_to_ship', '>', 0)
            ->whereNull('packed_at')
            ->whereNull('packer_user_id');

        $this->addField('ID', 'orders_products.id');
        $this->addField('Order ID', 'order.id');
        $this->addField('Order Number', 'order.order_number');
        $this->addField('Status', 'order.status_code');
        $this->addField('Status Code', 'order.status_code');
        $this->addField('Order Placed At', 'order.order_placed_at', 'datetime');
        $this->addField('Updated At', 'order.updated_at', 'datetime');
        $this->addField('Inventory Source Warehouse ID', 'inventory_source.warehouse_id', 'numeric');
        $this->addField('Inventory Source Warehouse Code', 'inventory_source.warehouse_code');
        $this->addField('Inventory Source Shelf Location', 'inventory_source.shelve_location');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('order.order_number', "%$value%");
            })
        );
    }
}
