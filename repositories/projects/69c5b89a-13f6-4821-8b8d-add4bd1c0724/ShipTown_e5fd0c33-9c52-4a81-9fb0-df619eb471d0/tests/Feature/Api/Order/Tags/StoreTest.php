<?php

namespace Tests\Feature\Api\Order\Tags;
use App\Models\Taggable;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\User;
use Tests\TestCase;
use function NightwatchAgent_kden27khxA4QoEfj\React\Promise\all;

class StoreTest extends TestCase
{
    private string $uri = 'api/order/tags';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $order = Order::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'order_id' => $order->id,
            'tags' => ['tags1', 'tags2'],
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertCount(2, $order->tags);

        $taggables = \App\Models\Taggable::where('taggable_type', \App\Models\Order::class)
            ->where('taggable_id', $order->id)
            ->get();

        foreach ($taggables as $taggable) {
            $this->assertNotNull($taggable->tag_name);
        }
    }
}
