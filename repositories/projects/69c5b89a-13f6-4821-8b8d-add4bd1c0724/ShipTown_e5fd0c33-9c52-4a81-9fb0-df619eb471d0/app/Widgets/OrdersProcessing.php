<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;

class OrdersProcessing extends AbstractWidget
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
        $count = \App\Models\Order::where(['status_code' => 'processing'])->count();

        return view('widgets.orders_processing', [
            'config' => $this->config,
            'count' => $count,
        ]);
    }
}
