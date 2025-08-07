<?php

namespace App\Modules\Webhooks\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use App\Modules\Webhooks\src\Models\PendingWebhook;
use App\Modules\Webhooks\src\Services\SnsService;
use Aws\Result;
use Exception;

/**
 * Class PublishOrdersWebhooksJob.
 */
class PublishInventoryMovementWebhooksJob extends UniqueJob
{
    public function handle()
    {
        Warehouse::query()
            ->get('id')
            ->each(function (Warehouse $warehouse) {
                $this->publishWarehouseWebhooks($warehouse->getKey());
            });
    }

    private function publishWarehouseWebhooks(int $warehouse_id)
    {
        $query = PendingWebhook::query()
            ->selectRaw('modules_webhooks_pending_webhooks.*')
            ->leftJoin(
                'inventory_movements',
                'inventory_movements.id',
                '=',
                'modules_webhooks_pending_webhooks.model_id'
            )
            ->where([
                'model_class' => InventoryMovement::class,
                'reserved_at' => null,
                'published_at' => null,
                'inventory_movements.warehouse_id' => $warehouse_id,
            ])
            ->orderBy('id')
            ->limit(100);

        $chunk = $query->get();

        while ($chunk->isNotEmpty()) {
            $pendingWebhookIds = $chunk->pluck('id');

            try {
                PendingWebhook::query()->whereIn('id', $pendingWebhookIds)->update(['reserved_at' => now()]);

                $response = $this->publishInventoryMovementMessage($chunk);

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

    private function publishInventoryMovementMessage($chunk): Result
    {
        $inventoryMovementsCollection = InventoryMovementResource::collection(
            InventoryMovement::query()
                ->whereIn('id', $chunk->pluck('model_id'))
                ->orderBy('id')
                ->with(['product', 'warehouse', 'user'])
                ->get()
        );

        $payload = collect(['InventoryMovements' => $inventoryMovementsCollection]);

        return SnsService::publishNew(
            $payload->toJson(),
            [
                'warehouse_code' => [
                    'DataType' => 'String',
                    'StringValue' => data_get($inventoryMovementsCollection->collection->first()->resource->toArray(), 'warehouse.code'),
                ],
            ]
        );
    }
}
