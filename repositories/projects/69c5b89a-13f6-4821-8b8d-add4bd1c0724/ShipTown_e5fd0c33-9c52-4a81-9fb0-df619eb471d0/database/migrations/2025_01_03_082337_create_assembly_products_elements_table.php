<?php

use App\Modules\AssemblyProducts\src\AssemblyProductsServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assembly_products_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assembly_product_id');
            $table->unsignedBigInteger('simple_product_id');
            $table->integer('required_quantity');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('assembly_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('simple_product_id')->references('id')->on('products')->onDelete('cascade');
        });

        AssemblyProductsServiceProvider::installModule();
    }
};
