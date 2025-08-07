<?php

namespace Tests\Modules\Api2cart\Api;

use App\Modules\Api2cart\Src\Api\Orders;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    public function test_fetch_orders_real_life()
    {
        $apiKey = env('API2CART_API_KEY');
        $storeKey = env('TEST_API2CART_STORE_KEY');

        if (!$storeKey || !$apiKey) {
            $this->markTestSkipped('API2CART_API_KEY or TEST_API2CART_STORE_KEY env variables are not set.');
        }

        $params = [
            'params' => 'force_all',
            'created_from' => now()->subYear(),
            'sort_by' => 'modified_at',
            'sort_direction' => 'asc',
            'count' => 1,
        ];

        $orders = Orders::get($storeKey, $params);

        $this->assertIsArray($orders, 'Orders should be an array or null');
        if (is_array($orders)) {
            $this->assertArrayHasKey(0, $orders, 'Orders array should have at least one order');
        }
    }
}

