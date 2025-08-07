<?php

use App\Modules\PointOfSaleConfiguration\src\PointOfSaleConfigurationServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modules_point_of_sale_configuration', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('next_transaction_number');
            $table->timestamps();
        });

        PointOfSaleConfigurationServiceProvider::enableModule();
    }
};
