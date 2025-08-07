<?php

namespace App\Modules\Api2cart\src\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Eloquent
 */
class Api2cartCallResponse extends Model
{
    use HasFactory;

    protected $table = 'modules_api2cart_call_responses';

    protected $fillable = [
        'type',
        'url',
        'processed_at',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}
