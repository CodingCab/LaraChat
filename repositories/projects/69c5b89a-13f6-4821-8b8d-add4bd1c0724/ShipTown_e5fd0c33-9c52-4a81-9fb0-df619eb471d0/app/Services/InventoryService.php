<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;

class InventoryService
{
    public static function sell(Inventory $inventory, float $quantityDelta, array $attributes = []): InventoryMovement
    {
        return InventoryService::adjust($inventory, $quantityDelta, array_merge($attributes, ['type' => InventoryMovement::TYPE_SALE]));
    }

    public static function transferIn(Inventory $inventory, float $quantityDelta, array $attributes = []): InventoryMovement
    {
        return InventoryService::adjust($inventory, $quantityDelta, array_merge($attributes, ['type' => InventoryMovement::TYPE_TRANSFER_IN]));
    }

    public static function transferOut(Inventory $inventory, float $quantityDelta, array $attributes = []): InventoryMovement
    {
        return InventoryService::adjust($inventory, $quantityDelta, array_merge($attributes, ['type' => InventoryMovement::TYPE_TRANSFER_OUT]));
    }

    public static function stocktake(Inventory $inventory, float $newQuantity, array $attributes = []): InventoryMovement
    {
        return InventoryService::adjust(
            $inventory,
            $newQuantity - $inventory->quantity,
            array_merge($attributes, [
                'type' => InventoryMovement::TYPE_STOCKTAKE,
                'quantity_after' => $newQuantity,
                'description' => 'stocktake',
            ])
        );
    }

    public static function adjust(Inventory $inventory, float $quantityDelta, array $attributes = []): InventoryMovement
    {
        $inventoryRefreshed = $inventory->refresh();

        return InventoryMovement::create(
            array_merge(
                [
                    'warehouse_code' => $inventoryRefreshed->warehouse_code,
                    'occurred_at' => now(),
                    'type' => InventoryMovement::TYPE_ADJUSTMENT,
                    'inventory_id' => $inventoryRefreshed->id,
                    'product_id' => $inventoryRefreshed->product_id,
                    'warehouse_id' => $inventoryRefreshed->warehouse_id,
                    'quantity_before' => $inventoryRefreshed->quantity,
                    'quantity_delta' => $quantityDelta,
                    'quantity_after' => $inventoryRefreshed->quantity + $quantityDelta,
                    'unit_cost' => $inventoryRefreshed->prices->cost,
                    'unit_price' => $inventoryRefreshed->prices->price,
                    'sales_tax_code' => $inventoryRefreshed->product->default_tax_code,
                    'description' => '',
                ],
                $attributes
            )
        );
    }
}
