<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPicture extends Model
{
    /** @use HasFactory<\Database\Factories\ProductPictureFactory> */
    use HasFactory;

    public $timestamps = true;
    const UPDATED_AT = null;
}
