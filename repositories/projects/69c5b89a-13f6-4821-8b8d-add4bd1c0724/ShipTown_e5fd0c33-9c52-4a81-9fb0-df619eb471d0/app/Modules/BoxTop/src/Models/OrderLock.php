<?php

namespace App\Modules\BoxTop\src\Models;

use App\BaseModel;

/**
 * @property int order_id
 */
class OrderLock extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'modules_boxtop_order_lock';

    /**
     * @var string[]
     */
    protected $fillable = [
        'order_id',
    ];
}
