<?php

namespace App\Widgets;

use App\Models\Widget;
use Arrilot\Widgets\AbstractWidget;

class BaseWidget extends AbstractWidget
{
    protected $name = null;

    protected $widgetId = null;

    public function __construct(array $config = [])
    {
        if ($this->name) {
            $widget = Widget::where('name', $this->name)->first();

            if ($widget) {
                $config = array_merge($config, $widget->config);
                $this->widgetId = $widget->getKey();
            }
        }

        parent::__construct($config);
    }
}
