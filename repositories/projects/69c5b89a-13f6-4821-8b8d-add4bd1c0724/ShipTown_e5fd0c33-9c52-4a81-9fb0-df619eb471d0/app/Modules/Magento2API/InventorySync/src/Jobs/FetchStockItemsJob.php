<?php

namespace App\Modules\Magento2API\InventorySync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Magento2API\InventorySync\src\Api\MagentoApi;
use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnection;
use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiProduct;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FetchStockItemsJob extends UniqueJob
{
    public function handle(): void
    {
        Magento2msiConnection::query()
            ->where('enabled', true)
            ->get()
            ->each(function (Magento2msiConnection $connection) {
                Magento2msiProduct::query()
                    ->where('connection_id', $connection->getKey())
                    ->where('magento_product_type', 'simple')
                    ->whereNotNull('magento_product_id')
                    ->whereNull('inventory_source_items_fetched_at')
                    ->chunkById(50, function (Collection $products) use ($connection) {
                        try {
                            usleep(100000); // Sleep for 0.1 seconds to avoid rate limiting

                            return $this->fetchStockItems($connection, $products);
                        } catch (Exception $exception) {
                            report($exception);
                            throw $exception;
                        }
                    });
            });
    }

    private function fetchStockItems(Magento2msiConnection $connection, Collection $products): bool
    {
        $response = MagentoApi::getInventorySourceItems($connection, $products->pluck('sku'));

        if ($response->failed()) {
            Log::error('Magento2msi - Failed to fetch stock items', [
                'connection_id' => $connection->getKey(),
                'response' => $response->json(),
            ]);

            return false;
        }

        Magento2msiProduct::query()
            ->whereIn('id', $products->pluck('id'))
            ->update([
                'source_assigned' => 0,
                'inventory_source_items_fetched_at' => now(),
                'inventory_source_items' => null,
                'sync_required' => null,
            ]);

        $map = collect($response->json('items', []))
            ->map(function ($item) use ($connection) {
                return [
                    'connection_id' => $connection->getKey(),
                    'source_assigned' => 1,
                    'sync_required' => null,
                    'sku' => $item['sku'],
                    'custom_uuid' => $item['sku'].'-'.$item['source_code'],
                    'source_code' => $item['source_code'],
                    'quantity' => $item['quantity'],
                    'status' => $item['status'],
                    'inventory_source_items_fetched_at' => now(),
                    'inventory_source_items' => json_encode($item),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

        Magento2msiProduct::query()->upsert($map->toArray(), ['connection_id', 'sku'], [
            'sync_required',
            'source_assigned',
            'source_code',
            'quantity',
            'status',
            'inventory_source_items_fetched_at',
            'inventory_source_items',
            'updated_at',
        ]);

        Log::info('Magento2msi - Fetched stock items', [
            'connection' => $connection->getKey(),
            'count' => $map->count(),
        ]);

        return true;
    }
}
