<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Models;

use App\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon $date
 * @property bool   $recalc_required
 * @property int    $max_inventory_id_checked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class InventoryMovementsDay extends BaseModel
{
    protected $table = 'inventory_movements_days';

    protected $fillable = [
        'date',
        'recalc_required',
        'max_inventory_id_checked',
    ];

    protected $casts = [
        'date' => 'date',
        'recalc_required' => 'boolean',
        'max_inventory_id_checked' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inventoryMovementDailyStatistics(): HasMany
    {
        return $this->hasMany(InventoryMovementsDailyStatistic::class, 'date', 'date');
    }
}
