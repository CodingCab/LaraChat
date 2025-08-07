<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\OrderTotals\src\Services\OrderTotalsService;
use Illuminate\Database\Seeder;

class OrderWithWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSingleProductOrder(true);
        $this->createSingleProductOrder(false);

        $this->createSingleProductOrderWithQty(true);
        $this->createSingleProductOrderWithQty(false);

        $this->createMultipleProductOrder(true);
        $this->createMultipleProductOrder(false);
    }

    private function createSingleProductOrder(bool $isLowerThan)
    {
        $product = Product::factory()->create([
            'weight' => $isLowerThan ? 20 : 35,
            'length' => $isLowerThan ? 10 : 50,
            'width' => $isLowerThan ? 10 : 50,
            'height' => $isLowerThan ? 10 : 50,
        ]);
        $order = Order::factory()->create();

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'sku_ordered' => $product->sku,
            'name_ordered' => $product->name,
            'quantity_ordered' => 1,
            'price' => $product->price
        ]);

        OrderTotalsService::updateTotals($order->id);
    }

    private function createSingleProductOrderWithQty(bool $isLowerThan)
    {
        $product = Product::factory()->create([
            'weight' => $isLowerThan ? 1 : 10,
            'length' => $isLowerThan ? 10 : 20,
            'width' => $isLowerThan ? 10 : 20,
            'height' => $isLowerThan ? 10 : 20,
        ]);
        $order = Order::factory()->create();

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'sku_ordered' => $product->sku,
            'name_ordered' => $product->name,
            'quantity_ordered' => 5,
            'price' => $product->price
        ]);

        OrderTotalsService::updateTotals($order->id);
    }

    private function createMultipleProductOrder($isLowerThan)
    {
        $products = Product::factory(2)->create([
            'weight' => $isLowerThan ? 1 : 20,
            'length' => $isLowerThan ? 10 : 40,
            'width' => $isLowerThan ? 10 : 40,
            'height' => $isLowerThan ? 10 : 40,
        ]);
        $order = Order::factory()->create();

        foreach ($products as $product) {
            OrderProduct::factory()->create([
                'order_id' => $order->getKey(),
                'product_id' => $product->getKey(),
                'sku_ordered' => $product->sku,
                'name_ordered' => $product->name,
                'quantity_ordered' => 2,
                'price' => $product->price
            ]);
        }

        OrderTotalsService::updateTotals($order->id);
    }
}
