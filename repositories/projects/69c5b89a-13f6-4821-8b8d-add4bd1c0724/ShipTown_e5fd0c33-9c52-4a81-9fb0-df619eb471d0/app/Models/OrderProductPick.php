<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $pick_id
 * @property int $order_product_id
 * @property float $quantity_picked
 * @property float $quantity_skipped_picking
 * @property OrderProduct $orderProduct
 * @property Pick $pick
 */
class OrderProductPick extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'picks_orders_products';

    protected $fillable = [
        'pick_id',
        'order_product_id',
        'quantity_picked',
        'quantity_skipped_picking',
    ];

    public function pick(): BelongsTo
    {
        return $this->belongsTo(Pick::class, 'pick_id', 'id')->withTrashed();
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }
}
