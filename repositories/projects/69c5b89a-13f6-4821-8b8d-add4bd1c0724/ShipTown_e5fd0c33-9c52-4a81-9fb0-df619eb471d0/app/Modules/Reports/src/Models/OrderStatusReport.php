<?php

namespace App\Modules\Reports\src\Models;

use App\Models\OrderStatus;
use Spatie\QueryBuilder\AllowedFilter;

class OrderStatusReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Order Statuses');
        $this->defaultSort = 'code';

        $this->baseQuery = OrderStatus::query();

        $this->addField('ID', 'orders_statuses.id', hidden: false);
        $this->addField('Code', 'orders_statuses.code', hidden: false);
        $this->addField('Name', 'orders_statuses.name', hidden: false);
        $this->addField('Order Active', 'orders_statuses.order_active', 'boolean', hidden: false);
        $this->addField('Order On Hold', 'orders_statuses.order_on_hold', 'boolean', hidden: false);
        $this->addField('Sync Ecommerce', 'orders_statuses.sync_ecommerce', 'boolean', hidden: false);
        $this->addField('Hidden', 'orders_statuses.hidden', 'boolean');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('orders_statuses.code', "%$value%")
                    ->orWhereLike('orders_statuses.name', "%$value%");
            })
        );
    }
}
