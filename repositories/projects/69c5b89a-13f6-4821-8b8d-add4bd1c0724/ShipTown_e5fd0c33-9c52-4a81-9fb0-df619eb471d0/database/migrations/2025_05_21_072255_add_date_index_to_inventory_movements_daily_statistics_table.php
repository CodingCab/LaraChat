<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX imds_date_desc_index ON inventory_movements_daily_statistics (date DESC)');
    }
};
