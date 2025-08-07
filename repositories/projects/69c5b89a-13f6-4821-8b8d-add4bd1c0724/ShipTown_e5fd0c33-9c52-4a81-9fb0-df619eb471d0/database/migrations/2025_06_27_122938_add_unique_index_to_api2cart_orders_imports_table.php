<?php

use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Api2cartOrderImports::query()->truncate();

        Schema::table('modules_api2cart_order_imports', function (Blueprint $table) {
            $table->unique(['connection_id', 'api2cart_order_id'], 'modules_api2cart_orders_imports_unique_index');
        });
    }
};
