<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->integer('line_number')->after('file_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->dropColumn('line_number');
        });
    }
};
