<?php

namespace App\Modules\Slack\src\Automations;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use App\Modules\Slack\src\Models\SlackConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendSlackNotificationAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        Log::debug('Automation Action', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            'comment' => $options,
        ]);

        /** @var SlackConfig $incomingWebhook */
        $incomingWebhook = SlackConfig::query()->firstOrCreate();

        if (! $incomingWebhook->incoming_webhook_url) {
            Log::warning('Slack Incoming Webhook URL not configured but "Send Slack notification" action used');

            return false;
        }

        Http::post($incomingWebhook->incoming_webhook_url, [
            'text' => 'Order #'.$this->order->order_number,
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => $options,
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => implode(
                            '',
                            [

                                '<',
                                URL::to('/orders?search='.$this->order->order_number),
                                '|',
                                'Order #'.$this->order->order_number,
                                '>',
                            ]
                        ),
                    ],
                    'accessory' => [
                        'type' => 'button',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Open Packsheet',
                        ],
                        'url' => URL::to('/order/packsheet/'.$this->order->id),
                        'style' => 'primary',
                    ],
                ],
            ],
        ]);

        return true;
    }
}
