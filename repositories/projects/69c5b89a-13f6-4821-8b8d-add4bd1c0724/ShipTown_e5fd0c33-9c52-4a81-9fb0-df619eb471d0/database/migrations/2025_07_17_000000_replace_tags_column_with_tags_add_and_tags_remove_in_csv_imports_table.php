<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            if (Schema::hasColumn('modules_csv_product_imports', 'tags')) {
                $table->dropColumn('tags');
            }
            if (!Schema::hasColumn('modules_csv_product_imports', 'tags_add')) {
                $table->string('tags_add')->nullable()->after('alias');
            }
            if (!Schema::hasColumn('modules_csv_product_imports', 'tags_remove')) {
                $table->string('tags_remove')->nullable()->after('tags_add');
            }
        });
    }
};
