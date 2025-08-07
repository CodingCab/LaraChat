<?php

namespace App\Modules\OrderTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class EnsureAllRecordsExistsJob extends UniqueJob
{
    private mixed $fromDateTime;

    private mixed $toDateTime;

    public function __construct($fromDateTime = null, $toDateTime = null)
    {
        $this->fromDateTime = $fromDateTime ?? now()->subHour();
        $this->toDateTime = $toDateTime ?? now();
    }

    public function handle()
    {
        DB::insert('
            INSERT INTO orders_products_totals (order_id, created_at, updated_at)
                SELECT
                  orders.id as order_id,
                  now(),
                  now()

                FROM orders
                LEFT JOIN orders_products_totals ON orders_products_totals.order_id = orders.id
                WHERE orders.order_placed_at BETWEEN ? AND ?
                    AND ISNULL(orders_products_totals.id)
        ', [$this->fromDateTime, $this->toDateTime ?? DB::raw('NOW()')]);
    }
}
