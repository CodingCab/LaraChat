<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;

class SetStatusCodeAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        $order = $this->order->refresh();

        if ($order->status_code === $options) {
            return true;
        }

        $order->update(['status_code' => $options]);

        return true;
    }
}
