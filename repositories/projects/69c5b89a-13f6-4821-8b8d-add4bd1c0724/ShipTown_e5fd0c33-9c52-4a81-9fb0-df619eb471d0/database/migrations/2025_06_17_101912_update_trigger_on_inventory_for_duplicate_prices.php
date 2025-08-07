<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_on_inventory;');

        DB::unprepared('
            CREATE TRIGGER trigger_on_inventory
            AFTER INSERT ON inventory
            FOR EACH ROW
            BEGIN
                INSERT INTO products_prices (inventory_id, product_id, warehouse_id, warehouse_code, created_at, updated_at)
                VALUES (NEW.id, NEW.product_id, NEW.warehouse_id, NEW.warehouse_code, now(), now())
                ON DUPLICATE KEY UPDATE
                    inventory_id = VALUES(inventory_id),
                    updated_at = now();
            END;
        ');
    }
};
