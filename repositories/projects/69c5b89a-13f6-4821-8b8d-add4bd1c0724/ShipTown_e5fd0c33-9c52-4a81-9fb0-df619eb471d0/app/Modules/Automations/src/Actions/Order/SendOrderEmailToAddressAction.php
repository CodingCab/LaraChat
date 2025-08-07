<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailToAddressAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        [$templateCode, $email] = array_map('trim', explode(',', $options . ','));

        if ($templateCode === '' || $email === '') {
            activity()->on($this->order)
                ->causedByAnonymous()
                ->log('Email template code or address missing');

            return false;
        }

        /** @var Order $order */
        $order = Order::query()
            ->whereKey($this->order->getKey())
            ->with('orderShipments', 'orderProducts', 'shippingAddress', 'billingAddress')
            ->first();

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', $templateCode)
            ->where('mailable', OrderMail::class)
            ->first();

        $emailMessage = new OrderMail($template, [
            'order' => $order->toArray(),
            'shipments' => $order->orderShipments->toArray(),
            'shipping_address' => $order->shippingAddress->toArray(),
            'billing_address' => $order->billingAddress->toArray(),
        ]);

        Mail::to($email)->send($emailMessage);

        return true;
    }
}
