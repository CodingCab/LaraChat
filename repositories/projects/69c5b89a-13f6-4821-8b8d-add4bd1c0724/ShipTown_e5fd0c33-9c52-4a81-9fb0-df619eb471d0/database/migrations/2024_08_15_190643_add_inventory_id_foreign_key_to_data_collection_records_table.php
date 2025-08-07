<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('data_collection_records', function (Blueprint $table) {
                $table->dropForeign('data_collection_records_inventory_id_foreign');
            });
        } catch (\Exception $e) {
            //
        }

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();
        });
    }
};
