<?php

namespace App\Modules\Reports\src\Models;

use App\Models\OrderShipment;
use Spatie\QueryBuilder\AllowedFilter;

class OrderShipmentReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Order Shipments';

        $this->baseQuery = OrderShipment::query()
            ->leftJoin('orders as order', 'orders_shipments.order_id', '=', 'order.id')
            ->leftJoin('users as user', 'orders_shipments.user_id', '=', 'user.id');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('order.order_number', "%$value%");
            })
        );

        $this->addField('id', 'orders_shipments.id', 'numeric');

        $this->addField('user name', 'user.name', hidden: false);
        $this->addField('carrier', 'orders_shipments.carrier', hidden: false);
        $this->addField('service', 'orders_shipments.service', hidden: false);
        $this->addField('shipping number', 'orders_shipments.shipping_number', hidden: false);
        $this->addField('order number', 'order.order_number', hidden: false);
        $this->addField('Order Status Code', 'order.status_code', hidden: false);
        $this->addField('created At', 'orders_shipments.created_at', 'datetime', hidden: false);

        $this->addField('count', 'orders_shipments.id', 'numeric', hidden: false, grouping: 'count');
    }
}
