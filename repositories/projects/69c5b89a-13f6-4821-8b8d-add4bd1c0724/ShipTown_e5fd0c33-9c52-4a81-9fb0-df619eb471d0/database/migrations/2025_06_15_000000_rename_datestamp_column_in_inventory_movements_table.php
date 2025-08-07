<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('inventory_movements', 'datestamp')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropColumn('datestamp');
            });
        }

        if (! Schema::hasColumn('inventory_movements', 'occurred_at_datestamp')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->unsignedInteger('occurred_at_datestamp')
                    ->storedAs("DATE_FORMAT(occurred_at, '%Y%m%d')")
                    ->after('occurred_at');
            });
        }
    }
};
