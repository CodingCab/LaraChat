<?php

namespace Tests\Browser\Routes\Reports;

use App\Models\InventoryMovement;
use Tests\DuskTestCase;

class HowToScheduleReportTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-movements';

    public function testSchedulingReport(): void
    {
        $this->visit('inventory-dashboard');

        $this->startRecording('Reports > How To Schedule a Report');

        $this->clickButton('#reports_link');
        $this->clickButton('#inventory_movements_report');

        $this->clickButton('#columns-button');
        $this->clickButton('#show-hide-columns-occurred_at');
        $this->clickButton('#show-hide-columns-filter-by-value-occurred_at');
        $this->clickButton('#modal-date-between-filter-select-date-range');
        $this->browser()->select('#modal-date-between-filter-select-date-range', 'this_week');
        $this->clickButton('#modal-date-between-filter-apply');
        $this->clickEscape();

        $this->clickButton('#options-button');
        $this->clickButton('#schedule-report-button');

        $reportName = "Today's Sales";
        $reportEmail = ', reports@myshiptown.com';
        $this->clickButton('#schedule-report-modal-name-input');
        $this->type(null, $reportName);
        $this->clickButton('#schedule-report-modal-email-input');
        $this->type(null, $reportEmail);
        $this->clickButton('#schedule-report-modal-ok-button');

        $parsedUrl = parse_url($this->browser()->driver->getCurrentURL());
        $path = $parsedUrl['path'];
        $pathWithParams = isset($parsedUrl['query']) ? $path . '?' . $parsedUrl['query'] : $path;
        $this->assertDatabaseHas('modules_scheduled_reports', [
            'uri' => $pathWithParams
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovement::factory()->count(10)->create([
            'occurred_at' => now()->subDays(1),
            'warehouse_code' => $this->testUser->warehouse->code,
            'warehouse_id' => $this->testUser->warehouse->id,
        ]);
    }
}
