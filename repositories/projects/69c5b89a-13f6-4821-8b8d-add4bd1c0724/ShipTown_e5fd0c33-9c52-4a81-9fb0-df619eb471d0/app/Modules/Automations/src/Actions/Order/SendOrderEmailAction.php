<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        /** @var Order $order */
        $order = Order::query()
            ->whereKey($this->order->getKey())
            ->with('orderShipments', 'orderProducts', 'shippingAddress', 'billingAddress')
            ->first();

        if (empty(trim($order->shippingAddress->email))) {
            activity()->on($this->order)
                ->causedByAnonymous()
                ->log('No email specified, skipping notification');

            return true;
        }

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', '<>', '')
            ->where(['code' => $options])
            ->first();

        $mailable = new OrderMail($template, [
            'order' => $order->toArray(),
            'shipments' => $order->orderShipments->toArray(),
        ]);

        try {
            Mail::to($order->shippingAddress->email)->send($mailable);

            activity()->on($order)
                ->causedByAnonymous()
                ->withProperties(['template_code' => $template->code])
                ->log('Email was send');
        } catch (\Exception $exception) {
            activity()->on($order)
                ->causedByAnonymous()
                ->log('Failed to send email: '.$exception->getMessage());

            return false;
        }

        return true;
    }
}
