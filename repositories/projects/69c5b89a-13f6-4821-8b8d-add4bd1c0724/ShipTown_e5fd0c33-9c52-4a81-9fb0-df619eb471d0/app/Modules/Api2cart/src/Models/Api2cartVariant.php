<?php

namespace App\Modules\Api2cart\src\Models;

use Illuminate\Database\Eloquent\Builder;

class Api2cartVariant extends Api2cartProductLink
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('variants', function (Builder $builder) {
            $builder->where(['api2cart_product_type' => 'variant']);
        });
    }
}
