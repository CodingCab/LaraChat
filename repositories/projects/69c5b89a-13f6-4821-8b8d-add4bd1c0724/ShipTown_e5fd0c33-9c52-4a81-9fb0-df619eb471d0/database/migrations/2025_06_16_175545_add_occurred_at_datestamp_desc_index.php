<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = collect(DB::select("SHOW INDEX FROM inventory_movements WHERE Key_name = 'occurred_at_datesamp_desc'"))->isNotEmpty();

        if (! $indexExists) {
            DB::statement('CREATE INDEX occurred_at_datestamp_desc ON inventory_movements (occurred_at_datestamp DESC)');
        }
    }
};
