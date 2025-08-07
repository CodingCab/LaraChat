<?php

namespace App\Widgets;

use App\Models\Order;
use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Facades\DB;

class TimeToZeroWidget extends AbstractWidget
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
        $data = [];

        $startDate = DB::raw('adddate(now(), -7)');

        $data['average_order_total'] = Order::where('order_placed_at', '>', $startDate)
            ->average('total');

        $data['orders_placed_count'] = Order::where('order_placed_at', '>', $startDate)
            ->count();

        $data['orders_closed_count'] = Order::where(['is_active' => false])
            ->where('order_closed_at', '>', $startDate)
            ->count();

        $data['active_orders_count'] = Order::query()
            ->where(['is_active' => true])
            ->count();

        $data['balance'] = $data['orders_closed_count'] - $data['orders_placed_count'];

        $data['staff_days_used'] = (int) DB::query()
            ->fromSub(
                Order::query()
                    ->select([
                        DB::raw('Date(order_closed_at) as date'),
                        DB::raw('count(distinct(packer_user_id)) as packers_count'),
                    ])
                    ->where('order_closed_at', '>', $startDate)
                    ->whereNotNull('packer_user_id')
                    ->groupBy([
                        'date',
                    ]),
                'staff_count_daily'
            )
            ->sum('packers_count');

        $data['avg_per_staff_per_day'] = round($data['staff_days_used'] ?? $data['orders_closed_count'] / $data['staff_days_used'], 1);

        $data['staff_days_required_for_balance_0'] = round($data['avg_per_staff_per_day'] ?? $data['orders_placed_count'] / $data['avg_per_staff_per_day'] / 5, 1);

        $data['staff_required_to_clear_in_5days'] = round($data['avg_per_staff_per_day'] ?? $data['active_orders_count'] / $data['avg_per_staff_per_day'] / 5, 1);

        $data['staff_working_day_value'] = round($data['avg_per_staff_per_day'] * $data['average_order_total'], 0);

        return view('widgets.time_to_zero_widget', [
            'config' => $this->config,
            'data' => $data,
        ]);
    }
}
