<?php

namespace App\Modules\Webhooks\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Modules\Webhooks\src\Models\PendingWebhook;
use App\Modules\Webhooks\src\Services\SnsService;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class PublishOrdersWebhooksJob.
 */
class PublishInventoryWebhooksJob extends UniqueJob
{
    public function handle()
    {
        Warehouse::query()
            ->get('code')
            ->each(function (Warehouse $warehouse) {
                $this->publishInventoryWebhooks($warehouse->code);
            });
    }

    private function publishInventoryWebhooks(string $warehouse_code): void
    {
        $query = PendingWebhook::query()
            ->selectRaw('modules_webhooks_pending_webhooks.*')
            ->leftJoin('inventory', 'inventory.id', '=', 'modules_webhooks_pending_webhooks.model_id')
            ->where([
                'model_class' => Inventory::class,
                'reserved_at' => null,
                'published_at' => null,
                'inventory.warehouse_code' => $warehouse_code,
            ])
            ->orderBy('modules_webhooks_pending_webhooks.id')
            ->limit(100);

        $chunk = $query->get();

        while ($chunk->isNotEmpty()) {
            $pendingWebhookIds = $chunk->pluck('id');

            try {
                PendingWebhook::query()->whereIn('id', $pendingWebhookIds)->update(['reserved_at' => now()]);

                $response = $this->publishInventoryMessage($chunk);

                Log::debug('Published Inventory Webhooks', [
                    'published_at' => now(),
                    'sns_message_id' => $response->get('MessageId'),
                ]);

                PendingWebhook::query()->whereIn('id', $pendingWebhookIds)->update([
                    'published_at' => now(),
                    'sns_message_id' => $response->get('MessageId'),
                ]);
            } catch (Exception $exception) {
                PendingWebhook::query()
                    ->whereIn('id', $pendingWebhookIds)
                    ->update(['reserved_at' => null, 'published_at' => null]);

                throw $exception;
            }

            usleep(200000); // 200ms
            $chunk = $query->get();
        }
    }

    private function publishInventoryMessage($chunk)
    {
        $ordersCollection = InventoryResource::collection(
            Inventory::query()
                ->whereIn('id', $chunk->pluck('model_id'))
                ->orderBy('id')
                ->with(['product', 'warehouse'])
                ->get()
        );

        $payload = collect(['Inventory' => $ordersCollection]);

        return SnsService::publishNew(
            $payload->toJson(),
            [
                'warehouse_code' => [
                    'DataType' => 'String',
                    'StringValue' => data_get($ordersCollection->collection->first()->resource->toArray(), 'warehouse.code'),
                ],
            ]
        );
    }
}
