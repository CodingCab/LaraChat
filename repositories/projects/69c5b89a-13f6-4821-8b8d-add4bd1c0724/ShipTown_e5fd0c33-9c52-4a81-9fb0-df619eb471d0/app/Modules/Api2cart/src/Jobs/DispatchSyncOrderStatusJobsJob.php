<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\OrderStatus;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;

class DispatchSyncOrderStatusJobsJob extends UniqueJob
{
    public function handle(): bool
    {
        $statusCodesToSync = OrderStatus::query()
            ->where('sync_ecommerce', true)
            ->pluck('code')
            ->toArray();

        Api2cartOrderImports::query()
            ->with(['order'])
            ->where(['order_status_in_sync' => false])
            ->orWhereNull('order_status_in_sync')
            ->chunk(50, function ($orderImports) use ($statusCodesToSync) {
                foreach ($orderImports as $orderImport) {
                    if (! $orderImport->order->status_code === $orderImport->order_status) {
                        $orderImport->update(['order_status_in_sync' => true]);
                        continue;
                    }

                    if (!in_array($orderImport->order->status_code, $statusCodesToSync)) {
                        $orderImport->update(['order_status_in_sync' => true]);
                        continue;
                    }

                    $orderImport->update(['order_status_in_sync' => false]);
                    SyncOrderStatus::dispatchSync($orderImport->order);
                }
            });

        return true;
    }
}
