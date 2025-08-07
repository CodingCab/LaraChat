<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Mail;

//use App\Services\PdfService;

class SendEmailToCustomerAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        parent::handle($options);

        /** @var Order $order */
        $order = Order::query()
            ->whereKey($this->order->getKey())
            ->with('orderShipments', 'orderProducts', 'shippingAddress', 'billingAddress')
            ->first();

        $variables = [
            'order' => $order->toArray(),
            'shipments' => $order->orderShipments->toArray(),
            'shipping_address' => $order->shippingAddress->toArray(),
            'billing_address' => $order->billingAddress->toArray(),
            'not_packed_products' => $order->orderProducts
                ->where('quantity_to_ship', '>', 0)
                ->map(fn(OrderProduct $product) => [
                    'sku' => $product->sku_ordered,
                    'name' => $product->name_ordered,
                    'quantity_to_ship' => $product->quantity_to_ship,
                ])->values()->toArray(),
        ];

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', $options)
            ->where('mailable', OrderMail::class)
            ->first();

        $email = new OrderMail($template, $variables);

        Mail::to($this->order->shippingAddress->email)
            ->send($email);

        return true;
    }
}
