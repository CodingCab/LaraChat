<?php

namespace Tests\Modules\DataCollectorDiscounts;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\OrderAddress;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollector\src\Jobs\RecountTotalsJob;
use App\Modules\DataCollectorDiscounts\src\DataCollectorDiscountsServiceProvider;
use App\Modules\DataCollectorDiscounts\src\Jobs\ApplyCustomerDiscountsJob;
use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected Warehouse $warehouse;

    protected Product $product4001;

    protected Product $product4005;

    protected OrderAddress $orderAddress;

    protected Discount $discount;

    protected DataCollection $dataCollection;

    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        DataCollectorDiscountsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4001 = Product::factory()->create(['sku' => '4001', 'price' => 10]);
        $this->product4005 = Product::factory()->create(['sku' => '4005', 'price' => 50]);

        $this->product4001->prices()
            ->update([
                'price' => 10,
                'sale_price' => '8',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->product4005->prices()
            ->update([
                'price' => 50,
                'sale_price' => '35',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->discount = Discount::firstOrCreate(['code' => 'TEST_DISCOUNT'], ['percentage_discount' => 10]);

        $this->orderAddress = OrderAddress::factory()->create(['discount_code' => $this->discount->code]);

        $this->dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransaction::class,
            'warehouse_id' => $this->warehouse->getKey(),
            'warehouse_code' => $this->warehouse->code,
            'shipping_address_id' => $this->orderAddress->getKey(),
            'billing_address_id' => $this->orderAddress->getKey(),
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $this->dataCollection->getKey(),
            'product_id' => $this->product4001->getKey(),
            'inventory_id' => $this->product4001->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 5,
            'unit_full_price' => 10,
            'unit_sold_price' => 9,
            'quantity_scanned' => 2,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $this->dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 25,
            'unit_full_price' => 50,
            'unit_sold_price' => 45,
            'quantity_scanned' => 3,
            'quantity_requested' => 0,
        ]);
    }

    #[Test]
    public function testExample()
    {
        ApplyCustomerDiscountsJob::dispatch($this->dataCollection);
        RecountTotalsJob::dispatch($this->dataCollection->getKey());

        $this->dataCollection->refresh();

        ray($this->dataCollection, $this->dataCollection->records()->get()->toArray());

        $this->assertEquals(153, $this->dataCollection->total_sold_price);
    }
}
