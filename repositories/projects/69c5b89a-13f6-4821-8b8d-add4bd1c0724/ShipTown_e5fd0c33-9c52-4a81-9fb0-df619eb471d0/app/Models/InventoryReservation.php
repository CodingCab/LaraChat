<?php

namespace App\Models;

use App\BaseModel;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $inventory_id
 * @property string $product_sku
 * @property string $warehouse_code
 * @property float $quantity_reserved
 * @property string $comment
 * @property Inventory $inventory
 */
class InventoryReservation extends BaseModel
{
    use HasFactory, LogsActivityTrait;

    protected static $recordEvents = ['created', 'deleted'];

    protected $fillable = [
        'inventory_id',
        'product_sku',
        'warehouse_code',
        'quantity_reserved',
        'comment',
        'custom_uuid',
    ];

    protected function casts(): array
    {
        return [
            'quantity_reserved' => 'float',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
