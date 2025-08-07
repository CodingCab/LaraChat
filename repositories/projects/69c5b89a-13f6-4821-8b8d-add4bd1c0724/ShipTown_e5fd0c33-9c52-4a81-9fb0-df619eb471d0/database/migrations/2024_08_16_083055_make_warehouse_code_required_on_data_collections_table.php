<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('data_collections', function (Blueprint $table) {
                $table->dropForeign('data_collections_warehouse_code_foreign');
            });
        } catch (\Exception $e) {
            //
        }

        Schema::table('data_collections', function (Blueprint $table) {
            $table->string('warehouse_code', 5)->nullable(false)->change();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });
    }
};
