<?php

namespace App\Widgets;

use App\Models\Pick;
use Illuminate\Support\Facades\DB;

class ProductsPickedCountsWidget extends AbstractDateSelectorWidget
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
        $count_per_user = Pick::query()
            ->select(['user_id', 'users.name', DB::raw('floor(sum(quantity_picked)) as total')])
            ->whereBetween('picks.created_at', [
                $this->getStartingDateTime(),
                $this->getEndingDateTime(),
            ])
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->groupBy(['user_id'])
            ->orderByDesc('total')
            ->get();

        $total_count = 0;

        foreach ($count_per_user as $count) {
            $total_count += $count['total'];
        }

        return view('widgets.products_picked_counts_widget', [
            'config' => $this->config,
            'count_per_user' => $count_per_user,
            'total_count' => $total_count,
        ]);
    }
}
