<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Models;

use App\BaseModel;

/**
 * @property int $warehouse_id
 */
class Configuration extends BaseModel
{
    protected $table = 'modules_inventory_reservations_configurations';

    protected $fillable = [
        'warehouse_id',
    ];
}
