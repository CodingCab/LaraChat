<?php

namespace App\Widgets;

class DateSelectorWidget extends AbstractDateSelectorWidget
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
        return view('widgets.date_selector_widget', [
            'config' => $this->config,
        ]);
    }
}
