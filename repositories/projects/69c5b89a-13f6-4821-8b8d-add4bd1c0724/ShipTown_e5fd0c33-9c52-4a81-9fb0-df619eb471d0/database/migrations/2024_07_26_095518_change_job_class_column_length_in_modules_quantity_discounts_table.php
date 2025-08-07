<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_quantity_discounts', function (Blueprint $table) {
            $table->string('job_class', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('modules_quantity_discounts', function (Blueprint $table) {
            $table->string('job_class', 50)->change();
        });
    }
};
