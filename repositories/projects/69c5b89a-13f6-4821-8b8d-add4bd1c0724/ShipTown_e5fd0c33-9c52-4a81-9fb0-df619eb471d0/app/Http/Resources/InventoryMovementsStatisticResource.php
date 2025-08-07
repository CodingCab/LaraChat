<?php

namespace App\Http\Resources;

use App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InventoryMovementsStatistic
 */
class InventoryMovementsStatisticResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'inventory_id' => $this->inventory_id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_code' => $this->warehouse_code,
            'quantity_sold' => $this->quantity_sold,
        ];
    }
}
