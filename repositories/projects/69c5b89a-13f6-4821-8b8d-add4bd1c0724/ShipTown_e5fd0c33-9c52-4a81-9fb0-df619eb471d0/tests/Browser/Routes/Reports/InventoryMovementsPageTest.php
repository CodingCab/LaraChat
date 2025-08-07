<?php

namespace Tests\Browser\Routes\Reports;

use App\Models\InventoryMovement;
use Tests\DuskTestCase;

class InventoryMovementsPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-movements';

    public function testSchedulingReport(): void
    {
        InventoryMovement::factory()->count(10)->create([
            'occurred_at' => now()->subDays(1),
            'warehouse_code' => $this->testUser->warehouse->code,
            'warehouse_id' => $this->testUser->warehouse->id,
        ]);

        $this->visit('inventory-dashboard');

        $this->startRecording('Reports > Inventory Movements');

        $this->clickButton('#reports_link');
        $this->clickButton('#inventory_movements_report');
        $this->clickButton('#columns-button');
        $this->clickButton('#show-hide-columns-occurred_at');
        $this->clickButton('#show-hide-columns-filter-by-value-occurred_at');
        $this->clickButton('#modal-date-between-filter-select-date-range');
        $this->browser()->select('#modal-date-between-filter-select-date-range', 'this_week');
        $this->clickButton('#modal-date-between-filter-apply');
        $this->clickEscape();

        $this->assertStringContainsString('filter%5Boccurred_at_between%5D=this%20week%20monday,this%20week%20sunday%2023%3A59%3A59', $this->browser()->driver->getCurrentURL());

        $this->clickButton('#options-button');
        $this->clickEscape();
    }
}
