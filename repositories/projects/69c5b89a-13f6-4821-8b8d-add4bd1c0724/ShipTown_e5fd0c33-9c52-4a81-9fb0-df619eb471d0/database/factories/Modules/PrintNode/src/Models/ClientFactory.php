<?php

namespace Database\Factories\Modules\PrintNode\src\Models;

use App\Modules\PrintNode\src\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'api_key' => $this->faker->text(20),
        ];
    }
}
