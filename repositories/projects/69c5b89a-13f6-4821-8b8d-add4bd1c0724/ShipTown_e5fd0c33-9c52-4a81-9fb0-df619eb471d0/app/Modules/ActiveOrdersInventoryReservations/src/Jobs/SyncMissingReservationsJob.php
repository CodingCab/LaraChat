<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\OrderProduct;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMissingReservationsJob extends UniqueJob
{
    public function __construct(
        public ?Carbon $createdAfter = null
    ) {
    }

    public function handle(): void
    {
        /** @var Configuration $config */
        $config = Configuration::query()->first();

        if (!$config || !$config->warehouse_id) {
            return;
        }

        // Find order products that should have reservations but don't
        $query = OrderProduct::query()
            ->select('orders_products.*')
            ->join('orders', 'orders.id', '=', 'orders_products.order_id')
            ->whereNotNull('orders_products.product_id')
            ->where('orders_products.quantity_to_ship', '>', 0)
            ->where('orders.is_active', true);

        // Filter by created date if specified
        if ($this->createdAfter) {
            $query->where('orders.created_at', '>=', $this->createdAfter);
        }

        $query
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('inventory_reservations')
                    ->whereRaw(
                        'inventory_reservations.custom_uuid = ' .
                        'CONCAT(?, ?, orders_products.order_id, ?, orders_products.id)',
                        [
                            ReservationsService::UUID_PREFIX,
                            ';order_id:',
                            ';order_product_id:'
                        ]
                    );
            })
            ->chunkById(100, function ($orderProducts) use ($config) {
                $this->createReservationsForOrderProducts($orderProducts, $config);
            });

        Log::info('SyncMissingReservationsJob completed', [
            'created_after' => $this->createdAfter?->toDateTimeString(),
        ]);
    }

    private function createReservationsForOrderProducts($orderProducts, Configuration $config): void
    {
        foreach ($orderProducts as $orderProduct) {
            try {
                $inventory = Inventory::query()
                    ->where('product_id', $orderProduct->product_id)
                    ->where('warehouse_id', $config->warehouse_id)
                    ->first();
                
                if (!$inventory) {
                    Log::warning('Inventory not found for product, skipping reservation creation', [
                        'product_id' => $orderProduct->product_id,
                        'warehouse_id' => $config->warehouse_id,
                        'order_product_id' => $orderProduct->id,
                    ]);
                    continue;
                }

                // Load relationships that might not be available if order product was created via SQL
                $orderProduct->load(['product', 'order']);
                
                InventoryReservation::create([
                    'inventory_id' => $inventory->id,
                    'product_sku' => $orderProduct->product->sku,
                    'warehouse_code' => $inventory->warehouse_code,
                    'quantity_reserved' => $orderProduct->quantity_to_ship,
                    'comment' => 'Order #'.$orderProduct->order->order_number,
                    'custom_uuid' => ReservationsService::getUuid($orderProduct),
                ]);

                Log::info('Created missing reservation for order product', [
                    'order_product_id' => $orderProduct->id,
                    'order_id' => $orderProduct->order_id,
                    'product_id' => $orderProduct->product_id,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to create reservation for order product', [
                    'order_product_id' => $orderProduct->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
