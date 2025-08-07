<?php

namespace App\Modules\Automations\src\Abstracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseOrderConditionAbstract
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function addQueryScope(Builder $query, $expected_value): Builder
    {
        return $query;
    }

    protected static function invalidateQueryIf(Builder $query, bool $shouldInvalidate, string $message = '1'): void
    {
        if ($shouldInvalidate) {
            $query->whereRaw('? = null', [$message]);
        }
    }

    protected static function invalidateQueryUnless($query, bool $shouldInvalidate): void
    {
        static::invalidateQueryIf($query, ! $shouldInvalidate);
    }

    public function isValid(?string $expected_value = ''): bool
    {
        return true;
    }
}
