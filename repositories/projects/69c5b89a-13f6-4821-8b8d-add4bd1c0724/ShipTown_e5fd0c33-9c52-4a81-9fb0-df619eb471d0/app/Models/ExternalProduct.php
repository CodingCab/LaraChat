<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalProduct extends Model
{
    protected $table = 'external_products';

    protected $fillable = [
        'type',
        'url',
        'raw_data',
        'product_id',

    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
