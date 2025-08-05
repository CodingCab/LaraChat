<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
