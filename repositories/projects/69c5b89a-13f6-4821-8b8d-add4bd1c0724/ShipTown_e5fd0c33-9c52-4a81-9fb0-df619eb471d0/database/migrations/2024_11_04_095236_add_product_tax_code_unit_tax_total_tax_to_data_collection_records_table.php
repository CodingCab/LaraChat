<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            if (!Schema::hasColumn('data_collection_records', 'sales_tax_code')) {
                $table->string('sales_tax_code')->nullable()->index()->after('total_price');
                $table->decimal('unit_tax', 20, 3)->nullable()->after('sales_tax_code');
                $table->decimal('total_tax', 20, 3)
                    ->nullable()
                    ->storedAs('ROUND(quantity_scanned * unit_tax, 3)')
                    ->comment('ROUND(quantity_scanned * unit_tax, 3)')
                    ->after('unit_tax');
            }
        });
    }
};
