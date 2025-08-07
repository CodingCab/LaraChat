<?php

namespace Database\Factories\Modules\SalesTaxes\src\Models;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleTaxFactory extends Factory
{
    protected $model = SaleTax::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word,
            'rate' => $this->faker->numberBetween(1, 100),
        ];
    }
}
