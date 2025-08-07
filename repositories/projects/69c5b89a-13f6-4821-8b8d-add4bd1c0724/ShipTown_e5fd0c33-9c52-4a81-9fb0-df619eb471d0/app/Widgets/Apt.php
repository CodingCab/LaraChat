<?php

namespace App\Widgets;

use App\Models\Order;

class Apt extends AbstractDateSelectorWidget
{
    protected $name = 'apt';

    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $statuses = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $this->statuses = Order::groupBy('status_code')->pluck('status_code')->toArray();

        $apt_seconds = (int) Order::query()
            ->selectRaw('AVG(TIME_TO_SEC(TIMEDIFF(order_closed_at, order_placed_at))) as apt')
            ->where(['is_active' => false])
            ->whereRaw('order_closed_at > order_placed_at')
            ->whereBetween('order_closed_at', [
                $this->getStartingDateTime(),
                $this->getEndingDateTime(),
            ])
            ->value('apt');

        return view('widgets.apt', [
            'config' => $this->config,
            'apt_string' => $this->timeDiffForPrez($apt_seconds),
            'statuses' => $this->statuses,
            'widget_id' => $this->widgetId,
            'widget_name' => $this->name,
        ]);
    }

    private function timeDiffForPrez(int $diffInSeconds)
    {
        $result = '';

        $data = [
            'd' => 86400,
            'h' => 3600,
            'm' => 60,
        ];

        foreach ($data as $k => $v) {
            if ($diffInSeconds >= $v) {
                $diff = floor($diffInSeconds / $v);
                $result .= " $diff".($diff > 1 ? $k : substr($k, 0, -1));
                $diffInSeconds -= $v * $diff;
            }
        }

        return $result;
    }
}
