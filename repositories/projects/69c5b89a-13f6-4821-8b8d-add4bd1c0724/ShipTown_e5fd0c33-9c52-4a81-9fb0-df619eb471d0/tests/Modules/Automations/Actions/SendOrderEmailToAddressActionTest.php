<?php

namespace Tests\Modules\Automations\Actions;

use App\Mail\OrderMail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Modules\Automations\src\Actions\Order\SendOrderEmailToAddressAction;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOrderEmailToAddressActionTest extends TestCase
{
    public function test_success_when_template_and_email_specified(): void
    {
        Mail::fake();

        /** @var MailTemplate $template */
        $template = MailTemplate::factory()->create([
            'code' => 'order_update',
            'subject' => 'Order updated',
            'mailable' => OrderMail::class,
            'html_template' => 'Your order was updated',
        ]);

        $order = Order::factory()->create();
        $action = new SendOrderEmailToAddressAction($order);

        $actionSucceeded = $action->handle($template->code.',test@example.com');

        $this->assertTrue($actionSucceeded, 'Action failed');
        Mail::assertSent(OrderMail::class, function (OrderMail $mail) {
            return $mail->hasTo('test@example.com');
        });
    }
}
