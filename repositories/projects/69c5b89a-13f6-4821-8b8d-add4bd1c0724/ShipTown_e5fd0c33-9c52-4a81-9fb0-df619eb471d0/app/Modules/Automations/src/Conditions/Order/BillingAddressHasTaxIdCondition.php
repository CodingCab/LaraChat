<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class BillingAddressHasTaxIdCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        $value = trim($expected_value ?? '');

        $expectsTrue = $value === '' || filter_var($value, FILTER_VALIDATE_BOOL);

        if ($expectsTrue) {
            return $query->whereHas('billingAddress', function (Builder $q) {
                $q->whereNotNull('tax_id_encrypted');
            });
        }

        return $query->where(function (Builder $query) {
            $query->whereDoesntHave('billingAddress')
                ->orWhereHas('billingAddress', function (Builder $q) {
                    $q->whereNull('tax_id_encrypted');
                });
        });
    }
}
