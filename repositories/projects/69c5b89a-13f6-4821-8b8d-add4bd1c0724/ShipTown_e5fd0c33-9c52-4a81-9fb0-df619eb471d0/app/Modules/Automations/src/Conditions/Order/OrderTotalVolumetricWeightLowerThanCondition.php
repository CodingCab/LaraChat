<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class OrderTotalVolumetricWeightLowerThanCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        return $query->whereHas('orderProductsTotals', function ($query) use ($expected_value) {
            $query->where('total_volumetric_weight', '<', $expected_value);
        });
    }
}
