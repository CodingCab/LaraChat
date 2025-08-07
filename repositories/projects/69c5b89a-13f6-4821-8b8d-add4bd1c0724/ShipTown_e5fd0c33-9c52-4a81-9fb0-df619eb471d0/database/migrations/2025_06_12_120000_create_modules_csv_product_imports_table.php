<?php

use App\Modules\CsvProductImports\src\CsvProductImportsServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $warehouseCodes = DB::table('warehouses')->pluck('code');

        Schema::create('modules_csv_product_imports', function (Blueprint $table) use ($warehouseCodes) {
            $table->id();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('sku')->nullable();
            $table->string('name')->nullable();
            $table->string('department')->nullable();
            $table->string('category')->nullable();
            $table->decimal('weight', 20, 3)->nullable();
            $table->decimal('length', 20, 3)->nullable();
            $table->decimal('height', 20, 3)->nullable();
            $table->decimal('width', 20, 3)->nullable();
            $table->string('alias')->nullable();
            $table->string('tags')->nullable();
            $table->decimal('price', 20, 3)->nullable();
            $table->decimal('sale_price', 20, 3)->nullable();
            $table->date('sale_price_start_date')->nullable();
            $table->date('sale_price_end_date')->nullable();
            $table->string('commodity_code')->nullable();
            $table->string('sales_tax_code')->nullable();
            $table->string('supplier')->nullable();

            foreach ($warehouseCodes as $code) {
                $table->decimal("price_$code", 20, 3)->nullable();
                $table->decimal("sale_price_$code", 20, 3)->nullable();
                $table->date("sale_price_start_date_$code")->nullable();
                $table->date("sale_price_end_date_$code")->nullable();
                $table->decimal("restock_level_$code", 20, 3)->nullable();
                $table->decimal("reorder_point_$code", 20, 3)->nullable();
                $table->string("shelve_location_$code")->nullable();
            }

            $table->timestamps();
        });

        CsvProductImportsServiceProvider::installModule();
    }
};
