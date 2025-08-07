<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class OrderProductFactory extends Factory
{
    public function definition(): array
    {
        // we will increase number of single line  orders
        $randomQuantityOrdered = Arr::random([1, 1, 1, 1, 2, 2, 3, 3]) * Arr::random([1, 1, 1, 1, 1, 1, 1, 1, 2, 3]);

        /** @var Product $product */
        $product = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();

        return [
            'order_id' => function () {
                return Order::factory()->create()->getKey();
            },
            'unit_tax' => $this->faker->randomFloat(2, 10, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 20),
            'unit_full_price' => $product->price,
            'unit_discount' => $this->faker->randomFloat(2, 0, 10),
            'unit_sold_price' => $product->price - $this->faker->randomFloat(2, 0, 10),
            'product_id' => $product->getKey(),
            'sku_ordered' => $product->sku,
            'name_ordered' => $product->name,
            'quantity_ordered' => $randomQuantityOrdered,
            'price' => $product->price
        ];
    }
}
