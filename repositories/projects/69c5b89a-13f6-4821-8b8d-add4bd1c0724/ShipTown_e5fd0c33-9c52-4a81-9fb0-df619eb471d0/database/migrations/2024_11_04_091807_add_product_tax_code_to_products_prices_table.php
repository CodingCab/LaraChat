<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('products_prices', 'sales_tax_code')) {
                $table->string('sales_tax_code')->nullable()->index()->after('price');
            }
        });
    }
};
