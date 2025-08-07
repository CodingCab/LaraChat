<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_api2cart_connections', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('id');
        });
    }
};
