<?php

namespace App\Widgets;

use App\Models\Order;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NewOrdersCounts extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $orders_counts = Order::query()
            ->select([
                DB::raw('date(order_placed_at) as date_placed_at'),
                DB::raw('count(*) as order_count'),
            ])
            ->whereDate('order_placed_at', '>', Carbon::now()->subDays(7))
            ->groupBy([
                DB::raw('date(order_placed_at)'),
            ])
            ->orderByDesc('date_placed_at')
            ->get();

        $total_count = $orders_counts->sum(function ($day) {
            return $day['order_count'];
        });

        return view('widgets.new_orders_counts', [
            'config' => $this->config,
            'new_order_counts' => $orders_counts,
            'total_count' => $total_count,
        ]);
    }
}
