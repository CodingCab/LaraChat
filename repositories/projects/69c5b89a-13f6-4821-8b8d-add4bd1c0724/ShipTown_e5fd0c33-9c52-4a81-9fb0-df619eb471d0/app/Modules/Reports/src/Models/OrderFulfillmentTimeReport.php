<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class OrderFulfillmentTimeReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Orders Fulfillment Time');

        $this->baseQuery = Order::query()
            ->whereNotNull('orders.order_closed_at');

        $this->addField('order number', 'orders.order_number');
        $this->addField('Status Code', 'orders.status_code', hidden: false);
        $this->addField('closed at', 'orders.order_closed_at', type: 'datetime');
        $this->addField('packed at', 'orders.packed_at', type: 'datetime');
        $this->addField('picked at', 'orders.picked_at', type: 'datetime');
        $this->addField('total shipped', DB::raw('1'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('within 24h', DB::raw('CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 24 THEN 1 ELSE 0 END'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('within 48h', DB::raw('CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 24 AND TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 48 THEN 1 ELSE 0 END'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('within 72h', DB::raw('CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 48 AND TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 72 THEN 1 ELSE 0 END'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('over 72h', DB::raw('CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 72 THEN 1 ELSE 0 END'), 'numeric', hidden: false, grouping: 'sum');
//        $this->addField(t('In 24h Percent'), DB::raw('ROUND(CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 24 THEN 1 ELSE 0 END)/SUM(1)*100)'), 'numeric', hidden: false);
//        $this->addField(t('In 48h Percent'), DB::raw('ROUND(CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 24 AND TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 48 THEN 1 ELSE 0 END)/SUM(1)*100)'), 'numeric', hidden: false);
//        $this->addField(t('In 72h Percent'), DB::raw('ROUND(CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 48 AND TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) <= 72 THEN 1 ELSE 0 END)/SUM(1)*100)'), 'numeric', hidden: false);
//        $this->addField(t('Over 72h Percent'), DB::raw('ROUND(CASE WHEN TIMESTAMPDIFF(hour, orders.order_placed_at, orders.order_closed_at) > 72 THEN 1 ELSE 0 END)/SUM(1)*100)'), 'numeric', hidden: false);

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('orders.order_number', "$value%")->orWhereLike('orders.order_number', "%$value%");
            })
        );
    }
}
