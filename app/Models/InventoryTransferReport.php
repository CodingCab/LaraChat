<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferReport extends Model
{
    use HasFactory;

    protected $table = 'data_collection_records';

    protected $fillable = [
        'total_quantity_adjusted',
        'total_quantity_adjusted_cost',
        'total_quantity_adjusted_sold_price',
    ];

    protected $casts = [
        'total_quantity_adjusted' => 'decimal:2',
        'total_quantity_adjusted_cost' => 'decimal:2',
        'total_quantity_adjusted_sold_price' => 'decimal:2',
    ];
}