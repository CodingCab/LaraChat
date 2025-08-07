<?php

namespace App\Modules\Automations\src\Abstracts;

use App\Models\Order;
use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

/**
 * @property-read Order $order
 */
abstract class BaseOrderActionAbstract
{
    public Order $order;
    public User|Authenticatable|null $user;

    public function __construct($order, $user = null)
    {
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = auth()->user();
        }

        $this->order = $order;
    }

    public function handle(string $options = ''): bool
    {
        Log::debug('automation.action.executing', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);

        return true;
    }
}
