<?php

namespace App\Http\Resources;

use App\Models\InventoryMovement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InventoryMovement
 */
class InventoryMovementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'warehouse_code' => $this->warehouse_code,
            'occurred_at' => $this->occurred_at,
            'type' => $this->type,
            'sequence_number' => $this->sequence_number,
            'custom_unique_reference_id' => $this->custom_unique_reference_id,
            'inventory_id' => $this->inventory_id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity_before' => $this->quantity_before,
            'quantity' => $this->quantity_delta,
            'quantity_delta' => $this->quantity_delta,
            'quantity_after' => $this->quantity_after,
            'sales_tax_code' => $this->sales_tax_code,
            'unit_tax' => $this->unit_tax,
            'total_tax' => $this->total_tax,
            'description' => $this->description,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'inventory' => InventoryResource::make($this->whenLoaded('inventory')),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'saleTax' => $this->whenNotNull($this->saleTax, function () {
                return new SaleTaxResource($this->saleTax);
            }),
        ];
    }
}
