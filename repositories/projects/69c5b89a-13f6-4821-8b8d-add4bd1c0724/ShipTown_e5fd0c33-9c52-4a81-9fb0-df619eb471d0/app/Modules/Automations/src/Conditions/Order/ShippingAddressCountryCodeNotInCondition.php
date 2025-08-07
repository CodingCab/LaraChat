<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class ShippingAddressCountryCodeNotInCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        return $query->whereHas('shippingAddress', function (Builder $query) use ($expected_value) {
            $query->whereNotIn('country_code', explode(',', $expected_value));
        });
    }
}
