<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        try {
            \Illuminate\Support\Facades\DB::unprepared('
                DROP TRIGGER trigger_on_products;
            ');
        } catch (\Exception $e) {
            //
        }

        \Illuminate\Support\Facades\DB::unprepared('
            CREATE TRIGGER trigger_on_products
            AFTER INSERT ON products
            FOR EACH ROW
            BEGIN
                INSERT INTO inventory (product_id, warehouse_id, warehouse_code, created_at, updated_at)
                SELECT new.id as product_id, warehouses.id as warehouse_id, warehouses.code as warehouse_code, now(), now() FROM warehouses;

                INSERT INTO products_aliases (product_id, alias, created_at, updated_at)
                VALUES (NEW.id, NEW.sku, now(), now())
                ON DUPLICATE KEY UPDATE product_id = NEW.id;
            END;
        ');

        \Illuminate\Support\Facades\DB::unprepared('
            CREATE TRIGGER trigger_on_inventory
            AFTER INSERT ON inventory
            FOR EACH ROW
            INSERT INTO products_prices (inventory_id, product_id, warehouse_id, warehouse_code, created_at, updated_at)
            VALUES (NEW.id, NEW.product_id, NEW.warehouse_id, NEW.warehouse_code, now(), now())
        ');
    }
};
