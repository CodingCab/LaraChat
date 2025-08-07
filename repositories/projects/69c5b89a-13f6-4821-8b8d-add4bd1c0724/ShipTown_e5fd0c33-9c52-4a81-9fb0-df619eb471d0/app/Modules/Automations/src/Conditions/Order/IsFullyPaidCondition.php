<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class IsFullyPaidCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        if ($expected_value === '') {
            $expected_value = 'true';
        }

        $expectedBoolValue = filter_var($expected_value, FILTER_VALIDATE_BOOL);

        return $query->whereRaw('(is_fully_paid = ?)', [$expectedBoolValue])
            ->whereRaw('product_line_count > 0');
        //        return $query->whereRaw('(is_fully_paid = ? AND product_line_count > 0)', [$expectedBoolValue]);
    }
}
