<?php

namespace App\Modules\Picklist\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\OrderProduct;
use App\Models\OrderProductPick;
use App\Models\Pick;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DistributePicksJob extends UniqueJob
{
    private ?Pick $pick;

    private string $key;

    public function __construct(?Pick $pick = null)
    {
        $this->pick = $pick;
        $this->key = implode('_', [get_class($this), data_get($this->pick, 'id', 0)]);
    }

    public function uniqueId(): string
    {
        return $this->key;
    }

    public function handle(): void
    {
        Cache::lock($this->key, 30)->get(function () {
            Pick::query()
                ->where('is_distributed', false)
                ->when($this->pick, function ($query) {
                    $query->where('id', $this->pick->id);
                })
                ->each(function (Pick $pick) {
                    DB::transaction(function () use ($pick) {
                        $this->distributePick($pick);
                    });
                });
        });
    }

    public function distributePick(Pick $pick): void
    {
        $pick->update([
            'quantity_distributed' => OrderProductPick::query()
                ->where('pick_id', $pick->id)
                ->sum('quantity_picked') ?? 0,
        ]);

        $orderProducts = OrderProduct::query()
            ->whereIn('id', $pick->order_product_ids)
            ->oldest('order_id')
            ->get();

        if ($pick->quantity_picked != 0) {
            $key = 'quantity_picked';
            $quantityToDistribute = $pick->quantity_picked - $pick->quantity_distributed;
        } else {
            $key = 'quantity_skipped_picking';
            $quantityToDistribute = $pick->quantity_skipped_picking - $pick->quantity_distributed;
        }

        foreach ($orderProducts as $orderProduct) {
            $quantity = min($quantityToDistribute, $orderProduct->quantity_to_pick);
            $orderProduct->fill([
                $key => $orderProduct->getAttribute($key) + $quantity,
            ]);
            $orderProduct->save();

            OrderProductPick::query()->create([
                'pick_id' => $pick->id,
                'order_product_id' => $orderProduct->id,
                $key => $quantity,
            ]);

            $pick->update(['quantity_distributed' => $pick->orderProductPicks()->sum('quantity_picked') ?? 0]);

            $quantityToDistribute -= $quantity;

            if ($quantityToDistribute <= 0) {
                $pick->update(['is_distributed' => true]);
                break;
            }
        }
    }
}
