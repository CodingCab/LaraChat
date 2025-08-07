<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    /** @use HasFactory<\Database\Factories\ProductDescriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'language_code',
        'description',
    ];
}
