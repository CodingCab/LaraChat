<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('picks', function (Blueprint $table) {
            $table->decimal('quantity_distributed', 20, 3)->default(0)->after('quantity_skipped_picking');
        });
    }
};
