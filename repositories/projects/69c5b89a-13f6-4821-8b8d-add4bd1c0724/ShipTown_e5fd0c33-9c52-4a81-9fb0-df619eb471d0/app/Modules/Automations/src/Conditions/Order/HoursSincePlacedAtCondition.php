<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class HoursSincePlacedAtCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        $trimmedValue = trim($expected_value);

        static::invalidateQueryIf($query, $trimmedValue === '', 'Hours since placed at value is empty');

        static::invalidateQueryIf($query, ! is_numeric($trimmedValue), 'Hours since placed at value is not a number');

        return $query->whereRaw('TIMESTAMPDIFF(hour, order_placed_at, now()) > ?', [intval($trimmedValue)]);
    }
}
