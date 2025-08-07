<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            if (!Schema::hasColumn('data_collection_records', 'calculated_unit_tax')) {
                $table->decimal('calculated_unit_tax', 20, 3)
                    ->nullable()
                    ->after('unit_tax');
            }

            if (!Schema::hasColumn('data_collection_records', 'calculated_total_tax')) {
                $table->decimal('calculated_total_tax', 20, 3)
                    ->nullable()
                    ->storedAs('ROUND(quantity_scanned * calculated_unit_tax, 3)')
                    ->comment('ROUND(quantity_scanned * calculated_unit_tax, 3)')
                    ->after('total_tax');
            }

            if (!Schema::hasColumn('data_collection_records', 'recalculate_unit_tax')) {
                $table->boolean('recalculate_unit_tax')
                    ->default(0)
                    ->after('calculated_total_tax');
            }
        });
    }
};
