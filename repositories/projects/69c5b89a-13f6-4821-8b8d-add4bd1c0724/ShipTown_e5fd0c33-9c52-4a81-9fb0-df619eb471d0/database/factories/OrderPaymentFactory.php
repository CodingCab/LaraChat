<?php

namespace Database\Factories;

use App\Models\OrderProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPaymentFactory extends Factory
{
    public function definition(): array
    {
        $orderProduct = OrderProduct::factory()->create();

        return [
            'paid_at' => $this->faker->date(),
            'name' => $this->faker->creditCardType(),
            'amount' => $this->faker->randomFloat(2, 5, 10),
            'additional_fields' => [
                'id' => $this->faker->uuid,
                'key' => 'value',
            ],
            'order_id' => $orderProduct->order_id,
        ];
    }
}
