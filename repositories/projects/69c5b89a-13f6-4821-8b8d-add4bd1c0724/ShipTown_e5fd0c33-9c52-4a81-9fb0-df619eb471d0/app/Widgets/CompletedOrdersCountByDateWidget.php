<?php

namespace App\Widgets;

use App\Models\Order;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompletedOrdersCountByDateWidget extends AbstractWidget
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
        $data = Order::query()
            ->select([
                DB::raw('date(order_closed_at) as date_closed_at'),
                DB::raw('count(*) as order_count'),
            ])
            ->whereDate('order_closed_at', '>', Carbon::now()->subDays(7))
            ->groupBy([
                DB::raw('date(order_closed_at)'),
            ])
            ->orderByDesc('date_closed_at')
            ->get();

        $total_count = $data->sum(function ($day) {
            return $day['order_count'];
        });

        return view('widgets.completed_orders_count_by_date_widget', [
            'config' => $this->config,
            'data' => $data,
            'total_count' => $total_count,
        ]);
    }
}
