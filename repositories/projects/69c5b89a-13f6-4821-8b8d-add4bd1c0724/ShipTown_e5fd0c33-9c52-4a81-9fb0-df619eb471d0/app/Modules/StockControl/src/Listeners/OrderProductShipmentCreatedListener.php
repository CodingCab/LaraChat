<?php

namespace App\Modules\StockControl\src\Listeners;

use App\Events\OrderProduct\OrderProductShipmentCreatedEvent;
use App\Models\InventoryMovement;
use Exception;

class OrderProductShipmentCreatedListener
{
    /**
     * @throws Exception
     */
    public function handle(OrderProductShipmentCreatedEvent $event): bool
    {
        $orderProductShipment = $event->orderProductShipment;

        if ($orderProductShipment->product_id === null) {
            return true;
        }

        if ($orderProductShipment->warehouse_id === null) {
            return true;
        }

        $uuid = implode('_', ['order_product_shipment_id', $orderProductShipment->getKey(), 'shipped']);
        $quantityDelta = $orderProductShipment->quantity_shipped * (-1);

        InventoryMovement::query()->create([
            'custom_unique_reference_id' => $uuid,
            'occurred_at' => now(),
            'type' => 'sale',
            'inventory_id' => $orderProductShipment->inventory->id,
            'product_id' => $orderProductShipment->inventory->product_id,
            'warehouse_code' => $orderProductShipment->inventory->warehouse_code,
            'warehouse_id' => $orderProductShipment->inventory->warehouse_id,
            'quantity_before' => $orderProductShipment->inventory->quantity,
            'quantity_delta' => $quantityDelta,
            'quantity_after' => $orderProductShipment->inventory->quantity + $quantityDelta,
            'unit_cost' => $orderProductShipment->inventory->prices->cost,
            'unit_price' => $orderProductShipment->inventory->prices->price,
            'description' => 'shipped',
        ]);

        return true;
    }
}
