<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the existing computed column
        if (Schema::hasColumn('data_collection_records', 'total_quantity_adjusted')) {
            Schema::table('data_collection_records', function (Blueprint $table) {
                $table->dropColumn('total_quantity_adjusted');
            });
        }

        // Add the column with new name
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('total_adjusted_quantity', 20)
                ->storedAs('IFNULL(total_transferred_in, 0) - IFNULL(total_transferred_out, 0)')
                ->comment('total_transferred_in - total_transferred_out')
                ->after('total_transferred_out');
        });
    }

    public function down(): void
    {
        // Drop the new column
        if (Schema::hasColumn('data_collection_records', 'total_adjusted_quantity')) {
            Schema::table('data_collection_records', function (Blueprint $table) {
                $table->dropColumn('total_adjusted_quantity');
            });
        }

        // Restore the old column
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('total_quantity_adjusted', 20)
                ->storedAs('IFNULL(total_transferred_in, 0) - IFNULL(total_transferred_out, 0)')
                ->comment('total_transferred_in - total_transferred_out')
                ->after('total_transferred_out');
        });
    }
};