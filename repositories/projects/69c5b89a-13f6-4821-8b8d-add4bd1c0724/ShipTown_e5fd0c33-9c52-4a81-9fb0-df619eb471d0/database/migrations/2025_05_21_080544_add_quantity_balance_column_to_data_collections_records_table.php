<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            // add new computed column (quantity_requested - total_quantity_transferred_in - total_quantity_transferred_out)
            $table->decimal('quantity_balance', 20)
                ->storedAs('IFNULL(quantity_requested, 0) - IFNULL(total_transferred_out, 0) - IFNULL(total_transferred_in, 0)')
                ->comment('quantity_requested - total_transferred_out - total_transferred_in')
                ->after('quantity_requested');
        });
    }
};
