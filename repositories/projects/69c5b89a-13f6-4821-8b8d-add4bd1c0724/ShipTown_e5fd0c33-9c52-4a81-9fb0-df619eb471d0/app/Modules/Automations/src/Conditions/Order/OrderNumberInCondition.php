<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class OrderNumberInCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        $orderNumbers = collect(explode(',', $expected_value))
            ->filter()
            ->transform(function ($record) {
                return trim($record);
            });

        return $query->whereIn('order_number', $orderNumbers);
    }
}
