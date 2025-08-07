<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\Api2cart\src\Api\Orders;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class SyncProductJob.
 */
class SyncOrderStatus extends UniqueJob
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function uniqueId(): string
    {
        return implode('-', [parent::uniqueId(), $this->order->id]);
    }

    public function handle(): bool
    {
        if ($this->order->orderStatus->sync_ecommerce === false) {
            return true;
        }

        $orderImport = Api2cartOrderImports::query()
            ->latest()
            ->where(['order_number' => $this->order->order_number])
            ->whereNotNull('connection_id')
            ->first();

        if ($orderImport === null) {
            // No api2cart products found for this order, nothing to sync
            return true;
        }

        try {
            // we assume it will be synced correctly... we will set it to false again if not
            // this is to prevent multiple jobs running for the same order
            $orderImport->update(['order_status_in_sync' => true]);

            $this->postUpdateRequest($orderImport);
        } catch (\Exception|GuzzleException $e) {
            $orderImport->update(['order_status_in_sync' => false]);
            report($e);
            return false;
        }

        return true;
    }

    /**
     * @param Api2cartOrderImports $orderImport
     * @throws \Exception|GuzzleException
     */
    public function postUpdateRequest(Api2cartOrderImports $orderImport): void
    {
        $response = Orders::update($orderImport->api2cartConnection->bridge_api_key, [
            'order_id' => $orderImport->api2cart_order_id,
            'order_status' => $this->order->status_code,
        ]);

        if ($response->isNotSuccess()) {
            throw new \Exception('Failed to update order status in Api2cart for order: ' . $this->order->order_number);
        }
    }
}
