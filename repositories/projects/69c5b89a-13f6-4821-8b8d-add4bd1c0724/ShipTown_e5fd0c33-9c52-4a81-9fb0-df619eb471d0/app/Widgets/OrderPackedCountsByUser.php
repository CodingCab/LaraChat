<?php

namespace App\Widgets;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderPackedCountsByUser extends AbstractDateSelectorWidget
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
        $count_per_user = Order::query()
            ->select([
                'packer_user_id',
                'users.name',
                DB::raw('count(*) as total'),
            ])
            ->whereBetween('packed_at', [
                $this->config['starting_date'],
                $this->config['ending_date'],
            ])
            ->leftJoin('users', 'packer_user_id', '=', 'users.id')
            ->groupBy(['packer_user_id'])
            ->orderByDesc('total')
            ->get();

        $total_count = 0;

        foreach ($count_per_user as $count) {
            $total_count += $count['total'];
        }

        return view('widgets.packed_today_by_user', [
            'config' => $this->config,
            'count_per_user' => $count_per_user,
            'total_count' => $total_count,
        ]);
    }
}
