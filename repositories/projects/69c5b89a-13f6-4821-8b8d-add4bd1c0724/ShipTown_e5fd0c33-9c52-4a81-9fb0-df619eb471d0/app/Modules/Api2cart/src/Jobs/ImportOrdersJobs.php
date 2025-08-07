<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Heartbeat;
use App\Modules\Api2cart\src\Api\Orders;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ImportOrdersJobs extends UniqueJob
{
    public bool $finishedSuccessfully;

    private Api2cartConnection $api2cartConnection;

    public function __construct(Api2cartConnection $api2cartConnection)
    {
        $this->api2cartConnection = $api2cartConnection;
        $this->finishedSuccessfully = false;
    }

    public function uniqueId(): string
    {
        return implode('-', [parent::uniqueId(), $this->api2cartConnection->id]);
    }

    public function handle(): void
    {
        Log::info('ImportOrdersJobs started', ['connection_id' => $this->api2cartConnection->getKey()]);

        do {
            $this->api2cartConnection->refresh();

            $ordersImported = $this->importOrders($this->api2cartConnection, 200);

            foreach ($ordersImported as $order) {
                $this->saveOrders($this->api2cartConnection, $order);
            }

            Heartbeat::query()->updateOrCreate([
                'code' => implode('_', ['api2cart', 'ImportOrdersJob', $this->api2cartConnection->getKey()]),
            ], [
                'error_message' => 'Web orders not fetched for last hour',
                'expires_at' => now()->addHour(),
            ]);

            Log::info('ImportOrdersJobs finished', [
                'connection_id' => $this->api2cartConnection->getKey(),
                'success' => $this->finishedSuccessfully,
            ]);
        } while (count($ordersImported) > 0);

        // finalize
        $this->finishedSuccessfully = true;
    }

    private function importOrders(Api2cartConnection $api2cartConnection, int $batchSize = 100): ?array
    {
        // initialize params
        $params = [
            'params' => 'force_all',
            'created_from' => $this->api2cartConnection->min_created_from ?? now()->subWeek()->floorDay(),
            'sort_by' => 'modified_at',
            'sort_direction' => 'asc',
            'count' => $batchSize,
        ];

        if ($api2cartConnection->magento_store_id) {
            $params['store_id'] = $api2cartConnection->magento_store_id;
        }

        if (isset($api2cartConnection->last_synced_modified_at)) {
            $params = Arr::add(
                $params,
                'modified_from',
                $api2cartConnection->last_synced_modified_at
            );
        }

        Log::debug('Importing orders from Api2cart', [
            'connection_id' => $api2cartConnection->getKey(),
            'params' => $params,
        ]);

        $orders = Orders::get($api2cartConnection->bridge_api_key, $params);

        if ($orders === null) {
            Log::warning('API2CART: Could not fetch orders');

            return null;
        }

        info('API2CART: Imported orders', [
            'connection_id' => $api2cartConnection->getKey(),
            'count' => count($orders),
        ]);

        return $orders;
    }

    private function saveOrders(Api2cartConnection $api2cartConnection, $order): void
    {
        Api2cartOrderImports::query()->updateOrCreate([
            'connection_id' => $api2cartConnection->getKey(),
            'api2cart_order_id' => data_get($order, 'order_id'),
        ], [
            'order_placed_at' => Carbon::createFromFormat(
                $order['create_at']['format'],
                $order['create_at']['value']
            ),
            'when_processed' => null,
            'order_status_in_sync' => null,
            'order_number' => data_get($order, 'id'),
            'shipping_method_name' => data_get($order, 'shipping_method.name', ''),
            'shipping_method_code' => data_get($order, 'shipping_method.additional_fields.code'),
            'status_code' => data_get($order, 'status.id', ''),
            'raw_import' => $order,
        ]);

        $this->updateLastSyncedTimestamp($api2cartConnection, $order);
    }

    private function updateLastSyncedTimestamp(Api2cartConnection $connection, $order): void
    {
        if (empty($order)) {
            return;
        }

        $lastTimeStamp = Carbon::createFromFormat(
            $order['modified_at']['format'],
            $order['modified_at']['value']
        );

        $connection->update([
            'last_synced_modified_at' => $lastTimeStamp->addSecond(),
        ]);
    }
}
