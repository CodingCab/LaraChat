<?php

namespace App\Modules\Api2cart\src\Models;

use Illuminate\Database\Eloquent\Builder;

class Api2cartSimpleProduct extends Api2cartProductLink
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('simple', function (Builder $builder) {
            $builder->where(['api2cart_product_type' => 'simple']);
        });
    }
}
