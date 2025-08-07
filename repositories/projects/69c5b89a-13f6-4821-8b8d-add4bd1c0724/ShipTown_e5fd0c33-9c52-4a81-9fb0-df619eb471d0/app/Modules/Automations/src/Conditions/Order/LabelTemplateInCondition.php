<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class LabelTemplateInCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        $shippingMethods = collect(explode(',', $expected_value))
            ->filter()
            ->transform(function ($record) {
                return trim($record);
            });

        if ($shippingMethods->isEmpty()) {
            $shippingMethods->add('');
        }

        return $query->whereIn('label_template', $shippingMethods);
    }
}
