<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->unique(['file_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->dropUnique(['file_id', 'line_number']);
        });
    }
};
