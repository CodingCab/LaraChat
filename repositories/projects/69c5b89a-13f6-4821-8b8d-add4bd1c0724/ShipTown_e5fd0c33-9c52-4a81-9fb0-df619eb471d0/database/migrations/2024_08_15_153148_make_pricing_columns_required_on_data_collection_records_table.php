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
            ->whereNull('unit_cost')
            ->chunk(5000, function ($records) {
                DataCollectionRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'unit_cost' => 0,
                        'unit_sold_price' => 0,
                        'unit_full_price' => 0,
                    ]);

                usleep(10000); // 10ms
            });

        DataCollectionRecord::query()
            ->whereNull('unit_sold_price')
            ->chunk(1000, function ($records) {
                DataCollectionRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'unit_cost' => 0,
                        'unit_sold_price' => 0,
                        'unit_full_price' => 0,
                    ]);

                usleep(10000); // 10ms
            });

        DataCollectionRecord::query()
            ->whereNull('unit_full_price')
            ->chunk(1000, function ($records) {
                DataCollectionRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'unit_cost' => 0,
                        'unit_sold_price' => 0,
                        'unit_full_price' => 0,
                    ]);

                usleep(10000); // 10ms
            });

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('unit_cost', 20, 3)->nullable(false)->change();
            $table->decimal('unit_sold_price', 20, 3)->nullable(false)->change();
            $table->decimal('unit_full_price', 20, 3)->nullable(false)->change();
        });
    }
};
