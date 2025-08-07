<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_api2cart_order_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('modules_api2cart_order_imports', 'status_code')) {
                $table->string('status_code', 50)->nullable()->after('raw_import');
            }
        });
    }
};
