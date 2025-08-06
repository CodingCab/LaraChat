<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = [
        'name',
        'url',
        'local_path',
        'branch',
        'last_pulled_at'
    ];
    
    protected $attributes = [
        'branch' => 'main',
    ];

    protected $casts = [
        'last_pulled_at' => 'datetime',
    ];
}
