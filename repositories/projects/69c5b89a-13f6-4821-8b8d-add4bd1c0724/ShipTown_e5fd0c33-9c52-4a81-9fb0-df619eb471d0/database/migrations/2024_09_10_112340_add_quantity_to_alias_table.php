<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products_aliases', function (Blueprint $table) {
            $table->decimal('quantity', 3)->default(1)->after('alias');
        });
    }
};
