<?php

namespace Database\Factories;

use App\Models\OrderProduct;
use App\Models\Product;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PickFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        $user = User::factory()->create();

        $orderProducts = OrderProduct::factory()
            ->count(rand(1, 5))
            ->create([
                'product_id' => $product->getKey(),
            ]);

        $quantityToPick = $orderProducts->sum('quantity_ordered');

        $shouldSkipPick = (rand(1, 20) === 1); // 5% chance of skipping pick

        return [
            'product_id' => $product->getKey(),
            'order_product_ids' => $orderProducts->pluck('id')->toArray(),
            'sku_ordered' => $product->sku,
            'name_ordered' => $product->name,
            'user_id' => $user->getKey(),
            'quantity_picked' => $shouldSkipPick ? 0 : $quantityToPick,
            'quantity_skipped_picking' => $shouldSkipPick ? $quantityToPick : 0,
        ];
    }
}
