<?php

namespace Tests\Feature\Reports;

use App\Models\InventoryMovement;
use App\Modules\Reports\src\Models\InventoryMovementsSummaryReport;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InventoryMovementsSummaryReportJsonTest extends TestCase
{
    #[Test]
    public function test_json_static_method_allows_parameters(): void
    {
        InventoryMovement::factory()->create(['occurred_at' => now()]);

        $resource = InventoryMovementsSummaryReport::json([
            'filter[occurred_at_between]' => 'today, now',
        ]);

        $data = $resource->toArray(request());

        $this->assertNotEmpty($data['data']);
        $this->assertSame(Carbon::now()->format('Ymd'), (string) data_get($data, 'data.0.date'));
    }
}
