<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'pack_quantity')) {
                $table->integer('pack_quantity')->nullable()->after('height');
            }
        });

        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('modules_csv_product_imports', 'pack_quantity')) {
                $table->integer('pack_quantity')->nullable()->after('width');
            }
        });
    }
};
