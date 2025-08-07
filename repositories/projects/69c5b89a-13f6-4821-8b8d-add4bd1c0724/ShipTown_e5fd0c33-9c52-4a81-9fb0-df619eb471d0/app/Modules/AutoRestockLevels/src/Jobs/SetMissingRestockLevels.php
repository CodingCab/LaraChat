<?php

namespace App\Modules\AutoRestockLevels\src\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetMissingRestockLevels implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?int $inventory_id;

    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return implode('-', [get_class($this)]);
    }

    public function __construct(?int $inventory_id = null)
    {
        $this->inventory_id = $inventory_id;
    }

    public function handle()
    {
        $collection = Inventory::query()
            ->when($this->inventory_id, fn ($query) => $query->where('id', '=', $this->inventory_id))
            ->where('quantity', '>', 0)
            ->where('restock_level', '=', 0)
            ->inRandomOrder()
            ->limit(100)
            ->get();

        if ($collection->isEmpty()) {
            return;
        }

        $collection->each(function (Inventory $inventory) {
            $newRestockLevel = $inventory->quantity + $inventory->quantity_incoming;

            if ($newRestockLevel > 3) {
                $newReorderPoint = ceil($newRestockLevel * 0.32);
            } else {
                $newReorderPoint = floor($newRestockLevel - 1);
            }

            $inventory->update([
                'restock_level' => ceil($newRestockLevel),
                'reorder_point' => $newReorderPoint,
            ]);
        });
    }
}
