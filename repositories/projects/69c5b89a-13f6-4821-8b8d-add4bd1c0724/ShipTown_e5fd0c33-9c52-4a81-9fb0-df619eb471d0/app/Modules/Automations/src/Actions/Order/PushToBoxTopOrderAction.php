<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Models\OrderShipment;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use App\Modules\BoxTop\src\Models\OrderLock;
use App\Modules\BoxTop\src\Services\BoxTopService;
use Exception;

class PushToBoxTopOrderAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        $newStatusCode = $options;

        try {
            // we create this record to make sure that multiple shipments are not created at the same time
            try {
                OrderLock::where('created_at', '<', now()->subMinutes(5))->forceDelete();

                $lock = OrderLock::create(['order_id' => $this->order->getKey()]);
            } catch (\Exception $exception) {
                // cannot lock it so another action is already running
                return false;
            }

            $shipment = OrderShipment::whereOrderId($this->order->getKey())->first();

            if ($shipment) {
                // order already shipped
                return true;
            }

            /** @var OrderShipment $shipment */
            $shipment = OrderShipment::create([
                'order_id' => $this->order->getKey(),
                'shipping_number' => '',
            ]);

            $response = BoxTopService::postOrder($this->order);

            if ($response->http_response->getStatusCode() === 201) {
                $shipment->carrier = 'BoxTop';
                $shipment->shipping_number = $response->toArray()['WarehouseJobNumber'];
                $shipment->save();

                $this->order->log('BoxTop pick created - '.$response->content);

                $this->order->status_code = $newStatusCode;
                $this->order->save();

                return true;
            }

            $shipment->delete();
            $lock->delete();
            $this->order->log('BoxTop pick FAILED - '.$response->content);
        } catch (Exception $exception) {
            if (isset($lock)) {
                $lock->forceDelete();
            }
            if (isset($shipment)) {
                $shipment->forceDelete();
            }
            $this->order->log('BoxTop pick FAILED - '.$exception->getMessage());

            return false;
        }

        return true;
    }
}
