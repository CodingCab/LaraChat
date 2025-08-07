<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPictureFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::first() ?? Product::factory()->create();

        return [
            'product_id' => $product->getKey(),
            'url' => url('/img/placeholder.png'),
        ];
    }
}
