<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_rmsapi_sales_imports', function (Blueprint $table) {
            $table->decimal('unit_cost', 20, 3)
                ->nullable()
                ->after('price');

            $table->decimal('total_sales_tax', 20, 3)
                ->nullable()
                ->after('unit_cost');
        });
    }
};
