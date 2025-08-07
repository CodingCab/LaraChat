<?php

namespace App\Modules\Picklist\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\OrderProductPick;
use App\Models\Pick;
use Cache;

class UnDistributeDeletedPicksJob extends UniqueJob
{
    public function handle(): void
    {
        Cache::lock(get_called_class(), 15)
            ->get(function () {
                $this->unDistributePicks();
            });
    }

    public function unDistributePicks(): void
    {
        Pick::query()
            ->onlyTrashed()
            ->where('quantity_distributed', '>', 0)
            ->eachById(function (Pick $pick) {
                OrderProductPick::query()
                    ->where('pick_id', $pick->id)
                    ->delete();
            });

        OrderProductPick::query()
            ->with(['orderProduct', 'pick'])
            ->onlyTrashed()
            ->where('quantity_picked', '>', 0)
            ->chunkById(100, function ($orderProductPicks) {
                $orderProductPicks->each(function (OrderProductPick $orderProductPick) {
                    $orderProductPick->update([
                        'quantity_picked' => 0,
                        'quantity_skipped_picking' => 0,
                    ]);

                    $orderProductPick->orderProduct->update([
                        'quantity_picked' => OrderProductPick::query()
                            ->where('order_product_id', $orderProductPick->order_product_id)
                            ->sum('quantity_picked') ?? 0,
                        'quantity_skipped_picking' => OrderProductPick::query()
                            ->where('order_product_id', $orderProductPick->order_product_id)
                            ->sum('quantity_skipped_picking') ?? 0,
                    ]);

                    $orderProductPick->pick->update([
                        'quantity_distributed' => $orderProductPick->pick->orderProductPicks()->sum('quantity_picked') ?? 0,
                    ]);
                });
            });
    }
}
