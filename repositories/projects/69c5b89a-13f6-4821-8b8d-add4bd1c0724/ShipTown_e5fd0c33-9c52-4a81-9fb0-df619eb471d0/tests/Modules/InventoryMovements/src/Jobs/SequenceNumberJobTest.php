<?php

namespace Tests\Modules\InventoryMovements\src\Jobs;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Services\InventoryService;
use Tests\TestCase;

class SequenceNumberJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsServiceProvider::enableModule();
    }

    public function testSameOccurredAtScenario()
    {
        $inventoryMovement = InventoryMovement::factory()->create([
            'sequence_number' => null,
        ]);

        InventoryMovement::factory()->create([
            'inventory_id' => $inventoryMovement->inventory_id,
            'occurred_at' => $inventoryMovement->occurred_at,
            'quantity_before' => $inventoryMovement->quantity_before,
            'quantity_after' => $inventoryMovement->quantity_after,
            'type' => 'adjustment',
            'sequence_number' => 1,
        ]);

        SequenceNumberJob::dispatchSync();

        $this->assertDatabaseMissing('inventory_movements', ['sequence_number' => null]);

        $this->assertTrue(true, 'Job dispatched successfully');
    }

    public function testBasicScenario(): void
    {
        $warehouse = Warehouse::factory()->create();

        $inventory = Product::factory()->create()->inventory()->first();
        $inventory2 = Product::factory()->create()->inventory()->first();

        $inventoryMovement01 = InventoryService::adjust($inventory, 20);
        $inventoryMovement02 = InventoryService::sell($inventory, -5);
        $inventoryMovement03 = InventoryService::stocktake($inventory, 7);

        ray(InventoryMovement::query()->get()->toArray());

        ray()->showQueries();
        SequenceNumberJob::dispatch();

        $inventoryMovement04 = InventoryService::sell($inventory, -7);
        $inventoryMovement05 = InventoryService::sell($inventory2, -7);

        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();
        $inventoryMovement04->refresh();
        $inventoryMovement05->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals(1, $inventoryMovement01->sequence_number);
        $this->assertEquals(2, $inventoryMovement02->sequence_number);
        $this->assertEquals(3, $inventoryMovement03->sequence_number);
        $this->assertEquals(4, $inventoryMovement04->sequence_number);

        $this->assertEquals(1, $inventoryMovement05->sequence_number);
    }
}
