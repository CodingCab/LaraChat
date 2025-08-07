<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class TotalQuantityToShipEqualsCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        static::invalidateQueryUnless($query, is_numeric($expected_value));

        return $query->whereHas('orderProductsTotals', function ($query) use ($expected_value) {
            $query->where('quantity_to_ship', '=', $expected_value);
        });
    }
}
