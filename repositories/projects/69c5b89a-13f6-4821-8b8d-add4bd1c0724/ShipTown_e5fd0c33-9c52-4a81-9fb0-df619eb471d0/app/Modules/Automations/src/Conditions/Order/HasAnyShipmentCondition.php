<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class HasAnyShipmentCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        $value = trim($expected_value ?? '');

        $expectsTrue = $value === '' || filter_var($value, FILTER_VALIDATE_BOOL);

        if ($expectsTrue) {
            return $query->whereHas('orderShipments');
        }

        return $query->whereDoesntHave('orderShipments');
    }
}
