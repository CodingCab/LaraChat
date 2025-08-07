<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('orders_addresses', 'locker_box_code')) {
                $table->string('locker_box_code')->nullable()->after('country_name');
            }
        });
    }
};
