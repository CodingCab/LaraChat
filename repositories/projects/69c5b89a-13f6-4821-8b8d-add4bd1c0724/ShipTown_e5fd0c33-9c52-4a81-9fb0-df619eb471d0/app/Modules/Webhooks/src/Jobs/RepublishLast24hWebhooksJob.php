<?php

namespace App\Modules\Webhooks\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Modules\Webhooks\src\Models\PendingWebhook;
use Illuminate\Support\Facades\DB;

/**
 * Class PublishOrdersWebhooksJob.
 */
class RepublishLast24hWebhooksJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            INSERT INTO modules_webhooks_pending_webhooks (model_class, model_id, created_at, updated_at)
                SELECT
                    ? as model_class,
                    inventory.id as model_id,
                    now() as created_at,
                    inventory.updated_at as updated_at
                FROM inventory

                LEFT JOIN modules_webhooks_pending_webhooks
                    ON modules_webhooks_pending_webhooks.model_id = inventory.id
                    AND modules_webhooks_pending_webhooks.model_class = ?
                    AND modules_webhooks_pending_webhooks.reserved_at IS NULL

                WHERE inventory.updated_at BETWEEN DATE_SUB(now(), INTERVAL 28 HOUR) and now()
                AND modules_webhooks_pending_webhooks.id is null;
        ", [Inventory::class, Inventory::class]);
    }
}
