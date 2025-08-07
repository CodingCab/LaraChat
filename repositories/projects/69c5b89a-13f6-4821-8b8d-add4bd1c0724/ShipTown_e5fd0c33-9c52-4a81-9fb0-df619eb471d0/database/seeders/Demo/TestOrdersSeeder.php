<?php

namespace Database\Seeders\Demo;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderComment;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->create_test_paid_order();

        $this->create_test_unpaid_order();

        $this->create_order_with_sku_not_in_our_database();

        $this->create_test_order_for_packing();

        $this->create_order_with_incorrect_address();

        $this->create_order_with_oversize_product();
    }

    protected function create_order_with_oversize_product(): void
    {
        Order::query()->where('order_number', 'OS100001')->forceDelete();

        /** @var Order $order1 */
        $order1 = Order::query()->create([
            'status_code' => 'new',
            'order_number' => 'OS100001',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        Order::query()->where('order_number', 'OS100002')->forceDelete();

        /** @var Order $order2 */
        $order2 = Order::query()->create([
            'status_code' => 'new',
            'order_number' => 'OS100002',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        Order::query()->where('order_number', 'OS100003')->forceDelete();

        /** @var Order $order3 */
        $order3 = Order::query()->create([
            'status_code' => 'new',
            'order_number' => 'OS100003',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create();
        $product1->attachTag('oversize');

        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        $product2->attachTag('oversize');

        /** @var Product $product3 */
        $product3 = Product::factory()->create();

        /** @var Product $product4 */
        $product4 = Product::factory()->create();

        OrderProduct::factory()->create(['order_id' => $order1->getKey(), 'product_id' => $product1->getKey()]);
        OrderProduct::factory()->create(['order_id' => $order1->getKey(), 'product_id' => $product3->getKey()]);

        OrderProduct::factory()->create(['order_id' => $order2->getKey(), 'product_id' => $product2->getKey()]);
        OrderProduct::factory()->create(['order_id' => $order2->getKey(), 'product_id' => $product3->getKey()]);

        OrderProduct::factory()->create(['order_id' => $order3->getKey(), 'product_id' => $product3->getKey()]);
        OrderProduct::factory()->create(['order_id' => $order3->getKey(), 'product_id' => $product4->getKey()]);
    }

    protected function create_order_with_sku_not_in_our_database(): void
    {
        $shippingAddress = OrderAddress::factory()->create([
            'country_name' => 'Ireland',
            'country_code' => 'IE',
        ]);

        Order::query()->where('order_number', 'T100001')->forceDelete();

        $order = Order::query()->create([
            'status_code' => 'new',
            'order_number' => 'T100001',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Product with SKU ordered does not exist in the system. This simulates scenario when product exists in remote system (Magento, Shopify etc) but no in our system.',
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Test order',
        ]);

        OrderProduct::create([
            'order_id' => $order->getKey(),
            'name_ordered' => 'Test product',
            'sku_ordered' => '123123123123123',
            'price' => 10,
            'quantity_ordered' => 1,
            'product_id' => null,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('IFNULL(total_order, 0)')]);
    }

    protected function create_test_order_for_packing(): void
    {
        Order::query()->where('order_number', 'T100002 - Packsheet')->forceDelete();

        $order = Order::query()->create([
            'status_code' => 'new',
            'order_number' => 'T100002 - Packsheet',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        Product::query()
            ->inRandomOrder()
            ->get()
            ->each(function (Product $product) use ($order) {
                return OrderProduct::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity_ordered' => rand(2, 6),
                    'price' => $product->price,
                    'name_ordered' => $product->name,
                    'sku_ordered' => $product->sku,
                ]);
            });

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('IFNULL(total_order, 0)')]);
    }

    protected function create_test_unpaid_order(): void
    {
        Order::query()->where('order_number', 'T100002 - Unpaid order')->forceDelete();

        $order = Order::factory()->create([
            'status_code' => 'new',
            'order_number' => 'T100002 - Unpaid order',
            'order_placed_at' => now()->subDays(3),
            'total_paid' => 0,
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        Product::query()
            ->inRandomOrder()
            ->get()
            ->each(function (Product $product) use ($order) {
                return OrderProduct::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity_ordered' => rand(1, 6),
                    'price' => $product->price,
                    'name_ordered' => $product->name,
                    'sku_ordered' => $product->sku,
                ]);
            });
    }

    private function create_order_with_incorrect_address(): void
    {
        Order::query()->where('order_number', 'T100003 - Incorrect address')->forceDelete();

        $orderAddress = OrderAddress::factory()->create([
            'address1' => 'This address is too long, over 50 characters, and some couriers might not accept it',
            'address2' => 'Test address',
            'city' => 'Dublin',
            'postcode' => 'D02EY47',
            'country_code' => 'IE',
            'country_name' => 'Ireland',
        ]);

        $order = Order::query()->create([
            'status_code' => 'new',
            'shipping_address_id' => $orderAddress->getKey(),
            'order_number' => 'T100003 - Incorrect address',
            'order_placed_at' => now()->subDays(3),
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Test with incorrect address (too long)',
        ]);

        Product::query()
            ->inRandomOrder()
            ->limit(1)
            ->get()
            ->each(function (Product $product) use ($order) {
                return OrderProduct::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity_ordered' => rand(2, 6),
                    'price' => $product->price,
                    'name_ordered' => $product->name,
                    'sku_ordered' => $product->sku,
                ]);
            });

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('IFNULL(total_order, 0)')]);
    }

    private function create_test_paid_order(): void
    {
        $order = Order::factory()->create([
            'status_code' => 'new',
            'order_placed_at' => now()->subDays(3),
            'shipping_address_id' => $this->createIrishShippingAddress()->getKey(),
        ]);

        Product::query()
            ->inRandomOrder()
            ->limit(2)
            ->get()
            ->each(function (Product $product) use ($order) {
                return OrderProduct::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity_ordered' => rand(2, 6),
                    'price' => $product->price,
                    'name_ordered' => $product->name,
                    'sku_ordered' => $product->sku,
                ]);
            });

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('IFNULL(total_order, 0)')]);
    }

    private function createIrishShippingAddress()
    {
        return OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
    }
}
