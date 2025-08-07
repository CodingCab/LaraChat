<?php

namespace Tests\Modules\Magento2API\InventorySync;

use PHPUnit\Framework\Attributes\Test;
use App\Modules\Magento2API\InventorySync\src\InventorySyncServiceProvider;
use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnection;
use App\Models\Product;
use Tests\TestCase;

class ProductTagAttachedEventListenerTest extends TestCase
{
    #[Test]
    public function test_creates_inventory_record_on_tag_attached(): void
    {
        InventorySyncServiceProvider::enableModule();

        $connection = Magento2msiConnection::factory()->create();
        $product = Product::factory()->create();

        $product->attachTag('Available Online');

        $this->assertDatabaseHas('modules_magento2msi_inventory_source_items', [
            'connection_id' => $connection->getKey(),
            'sku' => $product->sku,
        ]);
    }
}
