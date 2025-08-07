<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class RepublishWebhooksForSyncRequired extends UniqueJob
{
    public function handle(): void
    {
        DB::statement('
            INSERT INTO modules_webhooks_pending_webhooks (model_class, model_id, created_at, updated_at)
            SELECT
                ? as model_class,
                modules_rmsapi_products_imports.inventory_id as model_id,
                now() as created_at,
                now() as updated_at
            FROM modules_rmsapi_products_imports

            LEFT JOIN modules_webhooks_pending_webhooks
                ON modules_webhooks_pending_webhooks.model_class = ?
                AND modules_webhooks_pending_webhooks.model_id = modules_rmsapi_products_imports.inventory_id
                AND modules_webhooks_pending_webhooks.published_at IS NULL

            WHERE modules_rmsapi_products_imports.sync_required = 1
                AND modules_rmsapi_products_imports.processed_at IS NOT NULL
                AND modules_webhooks_pending_webhooks.id IS NULL

            LIMIT 1000
        ', [Inventory::class, Inventory::class]);
    }
}
