<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelTag extends Model
{
    protected $table = 'models_tags';

    protected $fillable = [
        'model_type',
        'model_id',
        'tag_name',
    ];
}
