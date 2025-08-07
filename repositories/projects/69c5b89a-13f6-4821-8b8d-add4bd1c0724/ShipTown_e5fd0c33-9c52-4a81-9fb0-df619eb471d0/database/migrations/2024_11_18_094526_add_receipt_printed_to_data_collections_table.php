<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collections', function (Blueprint $table) {
            if (!Schema::hasColumn('data_collections', 'receipt_printed')) {
                $table->boolean('receipt_printed')->default(false);
            }
        });
    }
};
