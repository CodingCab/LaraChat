<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('default_cost', 8, 3)->default(0)->after('type');
            $table->decimal('default_price', 8, 3)->default(0)->after('default_cost');
        });
    }
};
