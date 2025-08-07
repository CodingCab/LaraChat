<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Builder;

class OriginStatusCodeInCondition extends BaseOrderConditionAbstract
{
    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        static::invalidateQueryIf($query, trim($expected_value) === '');

        $statusCodes = collect(explode(',', $expected_value))
            ->filter()
            ->map(fn($record) => trim($record));

        return $query->whereIn('origin_status_code', $statusCodes);
    }
}
