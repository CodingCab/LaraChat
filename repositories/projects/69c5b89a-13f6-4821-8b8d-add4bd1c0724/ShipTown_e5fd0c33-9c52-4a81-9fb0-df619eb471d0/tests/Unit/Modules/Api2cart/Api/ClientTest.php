<?php

namespace Tests\Unit\Modules\Api2cart\Api;

use App\Modules\Api2cart\src\Api\Client;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientTest extends TestCase
{
    #[Test]
    public function test_get_method_uses_http_facade()
    {
        // Mock HTTP response
        Http::fake([
            'api.api2cart.com/v1.1/product.list.json*' => Http::response([
                'return_code' => 0,
                'return_message' => 'Success',
                'result' => [
                    'products' => []
                ]
            ], 200),
        ]);

        // Set API key in config
        config(['app.api2cart_api_key' => 'test-api-key']);

        // Make GET request
        $response = Client::GET('test-store-key', 'product.list.json', ['count' => 10]);

        // Assert response is successful
        $this->assertTrue($response->isSuccess());
        $this->assertEquals(0, $response->getReturnCode());
        $this->assertEquals('Success', $response->getReturnMessage());

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) {
            $url = parse_url($request->url());
            parse_str($url['query'] ?? '', $query);
            
            return str_contains($request->url(), 'api.api2cart.com/v1.1/product.list.json') &&
                   $query['api_key'] === 'test-api-key' &&
                   $query['store_key'] === 'test-store-key' &&
                   $query['count'] === '10';
        });
    }

    #[Test]
    public function test_get_method_without_store_key()
    {
        // Mock HTTP response
        Http::fake([
            'api.api2cart.com/v1.1/account.cart.list.json*' => Http::response([
                'return_code' => 0,
                'return_message' => 'Success',
                'result' => []
            ], 200),
        ]);

        // Set API key in config
        config(['app.api2cart_api_key' => 'test-api-key']);

        // Make GET request without store key
        $response = Client::GET('', 'account.cart.list.json', []);

        // Assert response is successful
        $this->assertTrue($response->isSuccess());

        // Verify HTTP was called without store_key
        Http::assertSent(function ($request) {
            $url = parse_url($request->url());
            parse_str($url['query'] ?? '', $query);
            
            return str_contains($request->url(), 'api.api2cart.com/v1.1/account.cart.list.json') &&
                   $query['api_key'] === 'test-api-key' &&
                   !isset($query['store_key']);
        });
    }

    #[Test]
    public function test_get_method_handles_error_responses()
    {
        // Mock HTTP error response
        Http::fake([
            'api.api2cart.com/v1.1/product.list.json*' => Http::response([
                'return_code' => 2,
                'return_message' => 'Incorrect API Key',
                'result' => []
            ], 200),
        ]);

        // Set API key in config
        config(['app.api2cart_api_key' => 'invalid-key']);

        // Make GET request
        $response = Client::GET('test-store-key', 'product.list.json', []);

        // Assert response is not successful
        $this->assertFalse($response->isSuccess());
        $this->assertEquals(2, $response->getReturnCode());
        $this->assertEquals('Incorrect API Key', $response->getReturnMessage());
    }
}