<?php

namespace App\Modules\OrderTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureCorrectTotalsJob extends UniqueJob
{
    private Carbon $fromDateTime;

    private Carbon $toDateTime;

    public function __construct($fromDateTime = null, $toDateTime = null)
    {
        $this->fromDateTime = $fromDateTime ?? now()->subHour();
        $this->toDateTime = $toDateTime ?? now();
    }

    public function handle(): void
    {
        DB::statement('
            UPDATE orders
            SET orders.recount_required = 1
            WHERE
                orders.order_placed_at BETWEEN ? AND ?
                AND orders.recount_required = 0
        ', [$this->fromDateTime, $this->toDateTime]);

        while ($this->extracted() != 0) {
            usleep(10000); // 10ms
        }
    }

    /**
     * @return void
     */
    public function extracted(): int
    {
        Schema::dropIfExists('tempTable_ordersToRecalculate');
        DB::statement('
            CREATE TEMPORARY TABLE tempTable_ordersToRecalculate AS
                SELECT
                    id as order_id
                FROM orders

                WHERE orders.recount_required = 1

                LIMIT 10
        ');

        Schema::dropIfExists('tempTable');
        DB::statement('
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    orders_products.order_id,
                    count(CASE WHEN orders_products.parent_product_id IS NULL THEN 1 END) as count_expected,
                    sum(CASE WHEN orders_products.parent_product_id IS NOT NULL THEN 0 ELSE orders_products.quantity_ordered END) as quantity_ordered_expected,
                    sum(orders_products.quantity_split) as quantity_split_expected,
                    sum(orders_products.quantity_picked) as quantity_picked_expected,
                    sum(orders_products.quantity_skipped_picking) as quantity_skipped_picking_expected,
                    sum(orders_products.quantity_not_picked) as quantity_not_picked_expected,
                    sum(orders_products.quantity_shipped) as quantity_shipped_expected,
                    sum(orders_products.total_products_shipped) as total_products_shipped_expected,
                    sum(orders_products.quantity_to_pick) as quantity_to_pick_expected,
                    sum(orders_products.quantity_to_ship) as quantity_to_ship_expected,
                    sum(orders_products.total_price) as total_price_expected,
                    max(orders_products.updated_at) as max_updated_at_expected

                FROM tempTable_ordersToRecalculate

                INNER JOIN orders_products
                    ON orders_products.order_id = tempTable_ordersToRecalculate.order_id

                GROUP BY orders_products.order_id;
        ');

        DB::update('
            INSERT INTO orders_products_totals (
                order_id,
                count,
                quantity_ordered,
                quantity_split,
                quantity_picked,
                quantity_skipped_picking,
                quantity_not_picked,
                quantity_shipped,
                total_products_shipped,
                quantity_to_pick,
                quantity_to_ship,
                total_price,
                max_updated_at
            )
            SELECT
                recalculations.order_id,
                IFNULL(recalculations.count_expected, 0),
                IFNULL(recalculations.quantity_ordered_expected, 0),
                IFNULL(recalculations.quantity_split_expected, 0),
                IFNULL(recalculations.quantity_picked_expected, 0),
                IFNULL(recalculations.quantity_skipped_picking_expected, 0),
                IFNULL(recalculations.quantity_not_picked_expected, 0),
                IFNULL(recalculations.quantity_shipped_expected, 0),
                IFNULL(recalculations.total_products_shipped_expected, 0),
                IFNULL(recalculations.quantity_to_pick_expected, 0),
                IFNULL(recalculations.quantity_to_ship_expected, 0),
                IFNULL(recalculations.total_price_expected, 0),
                IFNULL(recalculations.max_updated_at_expected, "2000-01-01 00:00:00")
            FROM tempTable AS recalculations
            ON DUPLICATE KEY UPDATE
                count = VALUES(count),
                quantity_ordered = VALUES(quantity_ordered),
                quantity_split = VALUES(quantity_split),
                quantity_picked = VALUES(quantity_picked),
                quantity_skipped_picking = VALUES(quantity_skipped_picking),
                quantity_not_picked = VALUES(quantity_not_picked),
                quantity_shipped = VALUES(quantity_shipped),
                total_products_shipped = VALUES(total_products_shipped),
                quantity_to_pick = VALUES(quantity_to_pick),
                quantity_to_ship = VALUES(quantity_to_ship),
                total_price = VALUES(total_price),
                max_updated_at = VALUES(max_updated_at);
        ');

        return DB::affectingStatement('
            UPDATE orders
            INNER JOIN tempTable AS recalculations
                ON recalculations.order_id = orders.id
            SET
                orders.product_line_count = recalculations.count_expected,
                orders.total_products = recalculations.total_price_expected,
                orders.recount_required = 0,
                orders.updated_at  = now()
        ');
    }
}
