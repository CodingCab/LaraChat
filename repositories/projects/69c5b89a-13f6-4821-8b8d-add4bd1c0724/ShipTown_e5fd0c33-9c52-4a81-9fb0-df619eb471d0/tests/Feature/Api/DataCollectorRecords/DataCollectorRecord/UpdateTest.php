<?php

namespace Tests\Feature\Api\DataCollectorRecords\DataCollectorRecord;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/data-collector-records/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransaction::class,
            'warehouse_id' => $warehouse->getKey(),
            'warehouse_code' => $warehouse->code,
        ]);

        /** @var DataCollectionRecord $dataCollectionRecord */
        $dataCollectionRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $product->getKey(),
            'warehouse_id' => $dataCollection->warehouse_id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'quantity_scanned' => 1,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(
            $this->uri.$dataCollectionRecord->id,
            [
                'quantity_scanned' => 24,
            ]
        );

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('data_collection_records', [
            'id' => $dataCollectionRecord->id,
            'quantity_scanned' => 24,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'product_id',
                'quantity_requested',
                'quantity_to_scan',
                'quantity_scanned',
            ],
        ]);
    }
}
