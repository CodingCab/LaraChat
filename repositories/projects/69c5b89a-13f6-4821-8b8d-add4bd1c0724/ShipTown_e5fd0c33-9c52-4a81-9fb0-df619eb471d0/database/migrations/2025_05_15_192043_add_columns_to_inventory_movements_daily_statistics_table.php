<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements_daily_statistics', function (Blueprint $table) {
            $table->boolean('recalc_required')->default(true)->after('id');
            $table->unsignedBigInteger('last_inventory_movement_id')->nullable()->after('inventory_id');

            $table->dropForeign('lm_sequence_number_fk');
            $table->dropColumn('last_inventory_movement_sequence_number');

            $table->foreign('last_inventory_movement_id', 'lm_sequence_number_fk')
                ->references('id')
                ->on('inventory_movements')
                ->onDelete('cascade');
        });
    }
};
