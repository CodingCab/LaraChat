<?php

use App\Models\DataCollectionRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DataCollectionRecord::query()
            ->whereNULL('warehouse_code')
            ->withTrashed()
            ->chunkById(5000, function ($records) {
                DataCollectionRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->withTrashed()
                    ->update([
                        'warehouse_code' => DB::raw('(SELECT warehouse_code FROM inventory WHERE inventory.id = inventory_id)'),
                        'warehouse_id' => DB::raw('(SELECT warehouse_id FROM inventory WHERE inventory.id = inventory_id)'),
                    ]);

                usleep(10000); // 10ms
            });

        DataCollectionRecord::query()
            ->whereNull('warehouse_id')
            ->chunkById(1000, function ($records) {
                DataCollectionRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'warehouse_id' => DB::raw('(SELECT warehouse_id FROM inventory WHERE inventory.id = inventory_id)'),
                    ]);

                usleep(10000); // 10ms
            });

        try {
            Schema::table('data_collection_records', function (Blueprint $table) {
                $table->dropForeign('data_collection_records_warehouse_code_foreign');
            });
        } catch (\Exception $e) {
            //
        }

        try {
            Schema::table('data_collection_records', function (Blueprint $table) {
                $table->dropForeign('data_collection_records_warehouse_id_foreign');
            });
        } catch (\Exception $e) {
            //
        }

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->string('warehouse_code', 5)->nullable(false)->change();
            $table->unsignedBigInteger('warehouse_id')->nullable(false)->change();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();
        });
    }
};
