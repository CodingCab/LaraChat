<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Models;

use App\BaseModel;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $last_inventory_movement_id
 */
class InventoryMovementsDailyStatistic extends BaseModel
{
    protected $table = 'inventory_movements_daily_statistics';

    protected $fillable = [
        'recalc_required',
        'date',
        'warehouse_code',
        'inventory_id',
        'last_inventory_movement_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'recalc_required' => 'boolean',
        'id' => 'integer',
        'date' => 'date',
        'inventory_id' => 'integer',
        'last_inventory_movement_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_code', 'code');
    }

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'last_inventory_movement_id', 'id');
    }
}
