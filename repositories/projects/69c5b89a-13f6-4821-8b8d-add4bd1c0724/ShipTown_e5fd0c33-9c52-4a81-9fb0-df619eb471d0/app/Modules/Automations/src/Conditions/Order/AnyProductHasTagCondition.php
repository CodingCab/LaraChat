<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class AnyProductHasTagCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        static::invalidateQueryIf($query, trim($expected_value) === '');

        $tagsArray = explode(',', $expected_value);

        return $query->whereHas('orderProducts', function (Builder $query) use ($tagsArray) {
            $query->whereHas('product', function (Builder $query) use ($tagsArray) {
                $query->withAllTags($tagsArray);
            });
        });
    }
}
