<?php

namespace Tests\Modules\Picklist;
use PHPUnit\Framework\Attributes\Test;

use App\Models\OrderProduct;
use App\Models\Pick;
use App\Modules\Picklist\src\Jobs\DistributePicksJob;
use App\Modules\Picklist\src\Jobs\UnDistributeDeletedPicksJob;
use App\Modules\Picklist\src\PicklistServiceProvider;
use Tests\TestCase;

class UnDistributeDeletedPicksJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PicklistServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality(): void
    {
        $pick = Pick::factory()->create();

        ray(Pick::query()->get()->toArray());
        DistributePicksJob::dispatch($pick);
        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => $pick->quantity_picked]);

        $pick->delete();

        UnDistributeDeletedPicksJob::dispatch();

        $this->assertEquals(0, $pick->orderProductPicks()->sum('quantity_picked'));

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => 0, 'deleted_at' => $pick->deleted_at]);
    }

    public function testIfDoesntTouchActiveRecords(): void
    {
        $pick = Pick::factory()->create();

        DistributePicksJob::dispatch($pick);

        UnDistributeDeletedPicksJob::dispatch();

        $pick->refresh();

        $this->assertEquals(OrderProduct::query()->sum('quantity_picked'), $pick->orderProductPicks()->sum('quantity_picked'));

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => $pick->quantity_distributed, 'deleted_at' => null]);
    }
}
