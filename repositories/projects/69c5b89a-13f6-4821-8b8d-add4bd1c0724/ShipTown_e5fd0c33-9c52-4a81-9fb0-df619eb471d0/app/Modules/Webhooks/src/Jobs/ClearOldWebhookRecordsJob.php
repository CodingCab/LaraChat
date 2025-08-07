<?php

namespace App\Modules\Webhooks\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Webhooks\src\Models\PendingWebhook;

/**
 * Class PublishOrdersWebhooksJob.
 */
class ClearOldWebhookRecordsJob extends UniqueJob
{
    public function handle()
    {
        $lastToDelete = PendingWebhook::query()
            ->where('created_at', '<', now()->subDays(7))
            ->latest('id')
            ->first();

        if ($lastToDelete === null) {
            return;
        }

        do {
            $recordsUpdated = PendingWebhook::query()
                ->where('id', '<', $lastToDelete->getKey())
                ->whereNotNull('published_at')
                ->limit(50000)
                ->forceDelete();

            usleep(200000); // 200ms
        } while ($recordsUpdated > 0);
    }
}
