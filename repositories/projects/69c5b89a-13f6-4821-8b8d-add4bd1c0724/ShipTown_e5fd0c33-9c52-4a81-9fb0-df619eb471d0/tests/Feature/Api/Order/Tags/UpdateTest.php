<?php

namespace Tests\Feature\Api\Order\Tags;

use App\Models\Order;
use App\Models\Taggable;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/order/tags';

    #[Test]
    public function test_tags_can_be_removed_from_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $order = Order::factory()->create();

        // attach two tags
        $this->actingAs($user, 'api')->postJson($this->uri, [
            'order_id' => $order->id,
            'tags' => ['tag1', 'tag2'],
        ])->assertOk();

        // update tags with only one value
        $this->actingAs($user, 'api')->postJson($this->uri, [
            'order_id' => $order->id,
            'tags' => ['tag1'],
        ])->assertOk();

        $order->refresh();

        $this->assertCount(1, $order->tags);
        $this->assertEquals('tag1', $order->tags->first()->name);

        $taggables = Taggable::where('taggable_type', Order::class)
            ->where('taggable_id', $order->id)
            ->get();

        foreach ($taggables as $taggable) {
            $this->assertNotNull($taggable->tag_name);
        }
    }
}

