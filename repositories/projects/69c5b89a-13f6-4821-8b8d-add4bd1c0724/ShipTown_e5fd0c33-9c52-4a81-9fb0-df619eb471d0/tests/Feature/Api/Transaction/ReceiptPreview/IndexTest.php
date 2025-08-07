<?php

namespace Tests\Feature\Api\Transaction\ReceiptPreview;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\OrderAddress;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/transaction/receipt-preview';

    private User $adminUser;

    private DataCollection $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $shippingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
        $billingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
        $storeAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create(['address_id' => $storeAddress->id]);

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $this->transaction = DataCollection::factory()->create([
            'name' => 'Test Transaction',
            'type' => \App\Models\DataCollectionTransaction::class,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $this->transaction->getKey(),
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'quantity_scanned' => rand(1, 10),
        ]);
    }

    #[Test]
    public function testIfCallReturnsOk()
    {
        $response = $this->actingAs($this->adminUser, 'api')->getJson($this->uri . '?id=' . $this->transaction->id);

        $response->assertOk();
    }

    public function testUserAccess(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson($this->uri . '?id=' . $this->transaction->id);

        $response->assertOk();
    }
}
