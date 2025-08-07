<?php

namespace App\Widgets;

use App\Models\Pick;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;

class PickCounts extends AbstractWidget
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

        $picksCount = Pick::query()
            ->whereDate('picked_at', '>', $startingDate)
            ->count();

        return view('widgets.pick_counts', [
            'config' => $this->config,
            'pickCount' => $picksCount,
        ]);
    }
}
