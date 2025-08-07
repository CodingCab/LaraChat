<?php

namespace App\Widgets;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CompletedStatusOrderCount extends AbstractDateSelectorWidget
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
        $status_order_counts = Order::query()
            ->select(['status_code', DB::raw('count(*) as order_count')])
            ->where(['is_active' => false])
            ->whereBetween('order_closed_at', [
                $this->getStartingDateTime(),
                $this->getEndingDateTime(),
            ])
            ->groupBy(['status_code'])
            ->orderByDesc('order_count')
            ->get();

        $total_count = $status_order_counts->sum(function ($day) {
            return $day['order_count'];
        });

        return view('widgets.completed_orders_counts', [
            'config' => $this->config,
            'status_order_counts' => $status_order_counts,
            'total_count' => $total_count,
        ]);
    }
}
