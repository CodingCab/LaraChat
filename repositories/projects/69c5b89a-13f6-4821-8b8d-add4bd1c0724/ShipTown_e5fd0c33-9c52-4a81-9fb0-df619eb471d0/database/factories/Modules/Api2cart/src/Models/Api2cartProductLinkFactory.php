<?php

namespace Database\Factories\Modules\Api2cart\src\Models;

use App\Models\Product;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class Api2cartProductLinkFactory extends Factory
{
    protected $model = Api2cartProductLink::class;

    public function definition(): array
    {
        $connection = Api2cartConnection::query()->inRandomOrder()->first() ?? Api2cartConnection::factory()->create();
        $product = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();

        return [
            'product_id' => $product->getKey(),
            'api2cart_connection_id' => $connection->getKey(),
            'api2cart_product_type' => null,
            'api2cart_product_id' => null,
            'api2cart_quantity' => null,
            'api2cart_price' => null,
            'api2cart_sale_price' => null,
            'api2cart_sale_price_start_date' => null,
            'api2cart_sale_price_end_date' => null,
            'last_fetched_at' => null,
            'last_fetched_data' => null,
        ];
    }
}
