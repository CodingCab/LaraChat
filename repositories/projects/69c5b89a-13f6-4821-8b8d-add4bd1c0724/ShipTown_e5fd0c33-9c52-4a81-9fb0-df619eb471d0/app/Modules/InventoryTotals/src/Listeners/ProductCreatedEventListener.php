<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Events\Product\ProductCreatedEvent;
use App\Models\InventoryTotal;
use App\Models\Taggable;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;

class ProductCreatedEventListener
{
    public function handle(ProductCreatedEvent $event): void
    {
        InventoryTotal::query()->insertOrIgnore(['product_id' => $event->product->getKey()]);

        $records = Taggable::query()
            ->where(['taggable_type' => Warehouse::class])
            ->get()
            ->map(function (Taggable $tag) use ($event) {
                return [
                    'tag_id' => $tag->tag_id,
                    'product_id' => $event->product->getKey(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->toArray();

        InventoryTotalByWarehouseTag::query()->insertOrIgnore($records);
    }
}
