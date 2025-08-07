<?php

namespace Database\Factories\Modules\Rmsapi\src\Models;

use App\Models\Warehouse;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class RmsapiConnectionFactory extends Factory
{
    protected $model = RmsapiConnection::class;

    public function definition(): array
    {
        $warehouse = Warehouse::query()->inRandomOrder()->first() ?? Warehouse::factory()->create();

        return [
            'warehouse_id' => $warehouse->id,
            'location_id' => $warehouse->code,
            'url' => $this->faker->url(),
            'username' => $this->faker->companyEmail(),
            'password' => $this->faker->password(),
            'price_field_name' => $this->faker->randomElement(['price', 'price_a', 'price_b', 'price_c']),
        ];
    }
}
