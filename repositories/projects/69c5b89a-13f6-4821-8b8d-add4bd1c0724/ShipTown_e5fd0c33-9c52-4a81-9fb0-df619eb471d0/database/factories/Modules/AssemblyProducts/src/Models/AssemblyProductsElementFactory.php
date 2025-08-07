<?php

namespace Database\Factories\Modules\AssemblyProducts\src\Models;

use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssemblyProductsElementFactory extends Factory
{
    protected $model = AssemblyProductsElement::class;

    public function definition(): array
    {
        return [
            'assembly_product_id' => \App\Models\Product::factory(),
            'simple_product_id' => \App\Models\Product::factory(),
            'required_quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}

