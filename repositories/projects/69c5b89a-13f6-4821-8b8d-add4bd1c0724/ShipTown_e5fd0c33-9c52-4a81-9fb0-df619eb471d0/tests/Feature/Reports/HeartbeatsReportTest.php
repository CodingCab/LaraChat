<?php

namespace Tests\Feature\Reports;

use App\Models\Heartbeat;
use App\Modules\Reports\src\Models\HeartbeatsReport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HeartbeatsReportTest extends TestCase
{
    #[Test]
    public function test_json_method_returns_records(): void
    {
        Heartbeat::create([
            'code' => 'test-heartbeat',
            'expires_at' => now(),
            'error_message' => 'Error',
        ]);

        $resource = HeartbeatsReport::json();

        $data = $resource->toArray(request());

        $this->assertNotEmpty($data['data']);
        $this->assertSame('test-heartbeat', data_get($data, 'data.0.code'));
    }
}
