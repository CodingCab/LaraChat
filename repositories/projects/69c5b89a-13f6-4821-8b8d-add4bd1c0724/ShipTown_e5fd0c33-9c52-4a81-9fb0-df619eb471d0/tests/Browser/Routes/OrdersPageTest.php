<?php

namespace Tests\Browser\Routes;

use App\Models\Order;
use App\Models\OrderProduct;
use App\User;
use Barryvdh\Reflection\DocBlock\Type\Collection;
use Tests\DuskTestCase;
use Throwable;

class OrdersPageTest extends DuskTestCase
{
    private string $uri = '/orders';

    /**
     * @throws Throwable
     */
    public function testBasicScenarios(): void
    {
        $this->browser()
            ->loginAs($this->user)
            ->visit($this->uri);

        $this->startRecording('How to find an order ?');
        $this->browser()->pause(1000);

        $this->say('How to find an order ?');
        $this->browser()
            ->pause(1000)
            ->typeAndEnter($this->order->order_number)
            ->waitForText($this->order->order_number)
            ->assertSee($this->order->status_code)
            ->pause(2000);
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        /** @var Order $order */
        $this->order = Order::factory()->create(['status_code' => 'paid']);

        /** @var Collection $orders */
        Order::factory(10)->create();

        Order::query()
            ->get()
            ->each(function (Order $order) {
                OrderProduct::factory(rand(1, 4))->create(['order_id' => $order->id]);
            });
    }
}
