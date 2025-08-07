<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::affectingStatement('
            UPDATE data_collections
            LEFT JOIN warehouses ON data_collections.destination_warehouse_id = warehouses.id

            SET data_collections.destination_warehouse_code = warehouses.code
            WHERE `destination_warehouse_id` IS NOT NULL
        ');
    }
};
