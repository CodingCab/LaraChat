<?php

namespace Tests\External\Webhooks\Controllers\SubscriptionController;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Webhooks\src\Services\SnsService;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        WebhooksServiceProviderBase::enableModule();

        SnsService::subscribeToTopic('https://test.com');

        $user = User::factory()->create();

        $this->actingAs($user, 'api');
    }

    protected function tearDown(): void
    {
        SnsService::client()->deleteTopic(['TopicArn' => SnsService::getConfiguration()->topic_arn]);

        parent::tearDown();
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $response = $this->getJson(route('api.modules.webhooks.subscriptions.index'));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data.response.Subscriptions'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                'service',
                'method',
                'response' => [
                    'Subscriptions' => [
                        '*' => [
                            'SubscriptionArn',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
