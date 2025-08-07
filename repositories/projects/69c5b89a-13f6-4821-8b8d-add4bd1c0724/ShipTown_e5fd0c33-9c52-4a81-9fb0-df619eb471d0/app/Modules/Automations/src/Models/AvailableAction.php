<?php

namespace App\Modules\Automations\src\Models;

use App\BaseModel;

/**
 * @property string class
 * @property string description
 */
class AvailableAction extends BaseModel
{
    protected $table = 'modules_automations_available_actions';

    protected $fillable = [
        'class',
        'description',
    ];
}
