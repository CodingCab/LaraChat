<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // do not merge - to be deleted
        DB::statement('
        CREATE OR REPLACE VIEW view_key_dates AS
        SELECT
          CURDATE() as date,
          DATE_ADD(CURDATE(), INTERVAL - WEEKDAY(now()) DAY) as this_week_start_date,
          DATE_ADD(CURDATE(), INTERVAL - DAY(now()) + 1 DAY) as this_month_start_date,
          DATE_ADD(CURDATE(), INTERVAL - DAYOFYEAR(now()) + 1 DAY) as this_year_start_date,

          now() as now;
        ');
    }
};
