<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'supplier_code')) {
                $table->string('supplier_code')->nullable()->after('supplier');
            }
        });

        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('modules_csv_product_imports', 'supplier_code')) {
                $table->string('supplier_code')->nullable()->after('supplier');
            }
        });
    }
};
