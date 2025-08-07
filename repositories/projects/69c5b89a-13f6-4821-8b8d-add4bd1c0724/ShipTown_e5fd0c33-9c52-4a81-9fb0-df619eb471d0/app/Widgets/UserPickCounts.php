<?php

namespace App\Widgets;

use App\Models\Pick;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserPickCounts extends AbstractWidget
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
        $startingDate = Carbon::now()->subDays(7);

        $count_per_user = Pick::query()
            ->select(['user_id', 'users.name', DB::raw('count(*) as total')])
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->whereDate('picks.created_at', '>', $startingDate)
            ->groupBy(['user_id'])
            ->get();

        $total_count = 0;

        foreach ($count_per_user as $count) {
            $total_count += $count['total'];
        }

        return view('widgets.user_pick_counts', [
            'config' => $this->config,
            'count_per_user' => $count_per_user,
            'total_count' => $total_count,
        ]);
    }
}
