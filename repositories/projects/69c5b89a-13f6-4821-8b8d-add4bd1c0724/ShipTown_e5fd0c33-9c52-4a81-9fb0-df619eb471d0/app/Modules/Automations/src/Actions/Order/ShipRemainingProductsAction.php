<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Warehouse;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Log;

class ShipRemainingProductsAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::query()->where(['code' => $options])->first();

        Log::debug('Automation Action', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);

        $order = $this->order->refresh();

        $order->orderProducts()->each(function (OrderProduct $orderProduct) use ($warehouse) {

            $orderProductShipment = new OrderProductShipment;
            $orderProductShipment->sku_shipped = $orderProduct->sku_ordered;
            $orderProductShipment->order_id = $orderProduct->order_id;
            $orderProductShipment->product_id = $orderProduct->product_id;
            $orderProductShipment->order_product_id = $orderProduct->getKey();
            $orderProductShipment->quantity_shipped = $orderProduct->quantity_to_ship;
            $orderProductShipment->warehouse_id = $warehouse->id;
            $orderProductShipment->save();

            $orderProduct->quantity_shipped += $orderProduct->quantity_to_ship;
            $orderProduct->save();
        });

        activity()->causedByAnonymous()->performedOn($order)->log('Automatically shipped all products');

        return true;
    }
}
