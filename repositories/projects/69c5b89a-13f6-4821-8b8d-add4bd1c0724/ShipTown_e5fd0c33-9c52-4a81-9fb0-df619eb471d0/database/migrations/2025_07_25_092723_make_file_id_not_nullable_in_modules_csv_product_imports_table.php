<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, set a default value for any existing null file_id records
        DB::table('modules_csv_product_imports')
            ->whereNull('file_id')
            ->update(['file_id' => 0]);

        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('file_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('file_id')->nullable()->change();
        });
    }
};
