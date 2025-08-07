<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductDescriptionFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::first() ?? Product::factory()->create();

        return [
            'product_id' => $product->getKey(),
            'language_code' => 'en',
            'description' => $this->faker->text(100),
        ];
    }
}
