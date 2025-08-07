<?php

namespace App\Widgets;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PackersAverage extends AbstractDateSelectorWidget
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
        $count_per_user = $this->getDailyTotals()
            ->orderByDesc('daily_average')
            ->get();

        $total_count = 0;

        foreach ($count_per_user as $count) {
            $total_count += $count['daily_average'];
        }

        return view('widgets.packers_average', [
            'config' => $this->config,
            'count_per_user' => $count_per_user,
            'total_count' => $total_count,
        ]);
    }

    /**
     * @return Order|Builder
     */
    private function getDailyTotals()
    {
        return Order::query()
            ->select([
                'packer_user_id',
                'users.name',
                DB::raw('count(*) as total'),
                DB::raw('count(distinct cast(packed_at as date)) as days_worked'),
                DB::raw('count(*) / count(distinct cast(packed_at as date)) as daily_average'),
            ])
            ->whereBetween('packed_at', [
                $this->config['starting_date'],
                $this->config['ending_date'],
            ])
            ->leftJoin('users', 'packer_user_id', '=', 'users.id')
            ->groupBy(['packer_user_id']);
    }
}
