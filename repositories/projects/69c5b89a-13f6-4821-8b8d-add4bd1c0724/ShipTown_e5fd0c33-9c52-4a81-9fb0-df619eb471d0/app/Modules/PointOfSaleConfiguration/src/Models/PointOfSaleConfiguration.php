<?php

namespace App\Modules\PointOfSaleConfiguration\src\Models;

use App\BaseModel;

/**
 * PointOfSaleConfiguration
 *
 * @property int $next_transaction_number
 */
class PointOfSaleConfiguration extends BaseModel
{
    protected $table = 'modules_point_of_sale_configuration';

    protected $fillable = [
        'next_transaction_number',
    ];
}
