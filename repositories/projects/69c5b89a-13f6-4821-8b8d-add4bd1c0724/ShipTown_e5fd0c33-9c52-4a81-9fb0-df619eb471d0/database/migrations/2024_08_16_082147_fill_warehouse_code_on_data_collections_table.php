<?php

use App\Models\DataCollection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DataCollection::query()
            ->whereNull('warehouse_code')
            ->withTrashed()
            ->chunkById(5000, function ($records) {
                DataCollection::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->withTrashed()
                    ->update([
                        'warehouse_code' => \DB::raw('(SELECT code FROM warehouses WHERE warehouses.id = warehouse_id)'),
                    ]);

                usleep(10000); // 10ms
            });
    }
};
