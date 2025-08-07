<?php

namespace Tests\Unit\Modules\Fakturowo;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Modules\Fakturowo\src\Api\FakturowoApi;
use App\Modules\Fakturowo\src\FakturowoServiceProvider;
use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use App\Modules\Fakturowo\src\Models\Invoice;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Modules\Fakturowo\src\OrderActions\RaiseFakturowoPLInvoice;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RaiseFakturowoPLInvoiceExceptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        FakturowoServiceProvider::enableModule();
    }

    #[Test]
    public function it_does_not_create_invoice_record_when_api_call_fails()
    {
        // Create configuration
        FakturowoConfiguration::create([
            'connection_code' => 'test_connection',
            'api_key' => 'test_api_key',
            'api_url' => 'https://test.fakturowo.pl/api'
        ]);

        // Create test billing address
        $billingAddress = OrderAddress::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'city' => 'Warsaw',
            'postcode' => '00-001',
            'address1' => 'Test Street 123',
            'company' => 'Test Company',
            'tax_id' => '1234567890'
        ]);

        // Create test order
        $order = Order::factory()->create([
            'billing_address_id' => $billingAddress->id,
            'order_number' => 'TEST-001',
            'total_shipping' => 20.00,
            'shipping_method_name' => 'Standard Shipping'
        ]);

        // Create test order product
        OrderProduct::factory()->create([
            'order_id' => $order->id,
            'quantity_ordered' => 5,
            'quantity_shipped' => 3,
            'quantity_invoiced' => 0,
            'name_ordered' => 'Test Product',
            'sku_ordered' => 'TEST-SKU',
            'price' => 30.00,
            'tax_rate' => 23
        ]);

        // Mock HTTP request to throw an exception
        Http::fake([
            '*' => Http::response('0\nAPI connection failed', 500)
        ]);

        // Ensure no invoice exists before the action
        $this->assertEquals(0, Invoice::count());
        $this->assertEquals(0, InvoiceOrderProduct::count());

        // Create and execute the action
        $action = new RaiseFakturowoPLInvoice($order);

        try {
            $action->handle('test_connection');
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            // Expected exception was thrown
            $this->assertEquals('Fakturowo.pl API request failed.', $e->getMessage());
        }

        // Assert no invoice records were created
        $this->assertEquals(0, Invoice::count(), 'Invoice record should not be created when API fails');
        
        // The InvoiceOrderProduct records might be created during the process
        // but they should not be linked to any invoice
        $unlinkedProducts = InvoiceOrderProduct::whereNull('invoice_id')->count();
        $linkedProducts = InvoiceOrderProduct::whereNotNull('invoice_id')->count();
        $this->assertEquals(0, $linkedProducts, 'No InvoiceOrderProduct records should be linked to an invoice when API fails');

        // Check that error was logged in activity
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'description' => 'Fakturowo.pl - Błąd podczas wystawiania faktury: Fakturowo.pl API request failed.'
        ]);
    }

    #[Test]
    public function it_throws_exception_with_api_error_message()
    {
        // Create configuration
        FakturowoConfiguration::create([
            'connection_code' => 'test_connection',
            'api_key' => 'test_api_key',
            'api_url' => 'https://test.fakturowo.pl/api'
        ]);

        // Create test billing address
        $billingAddress = OrderAddress::factory()->create();

        // Create test order
        $order = Order::factory()->create([
            'billing_address_id' => $billingAddress->id
        ]);

        // Create test order product
        OrderProduct::factory()->create([
            'order_id' => $order->id,
            'quantity_shipped' => 1,
            'quantity_invoiced' => 0
        ]);

        // Mock HTTP request to return API error
        Http::fake([
            '*' => Http::response("0\nInvalid tax ID format", 200)
        ]);

        // Create and execute the action
        $action = new RaiseFakturowoPLInvoice($order);

        try {
            $action->handle('test_connection');
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            // Expected exception was thrown with API error message
            $this->assertEquals('Invalid tax ID format', $e->getMessage());
        }

        // Assert no invoice records were created
        $this->assertEquals(0, Invoice::count(), 'Invoice record should not be created when API returns error');
    }
}