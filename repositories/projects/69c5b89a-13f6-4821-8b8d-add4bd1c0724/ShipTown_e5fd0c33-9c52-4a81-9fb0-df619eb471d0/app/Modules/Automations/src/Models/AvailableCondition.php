<?php

namespace App\Modules\Automations\src\Models;

use App\BaseModel;

/**
 * @property string class
 * @property string description
 */
class AvailableCondition extends BaseModel
{
    protected $table = 'modules_automations_available_conditions';

    protected $fillable = [
        'class',
        'description',
    ];
}
