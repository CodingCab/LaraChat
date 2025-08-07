<?php

namespace Tests\Feature\Reports;

use App\Models\Warehouse;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NumericWarehouseColumnTest extends TestCase
{
    #[Test]
    public function purchase_orders_report_handles_numeric_code(): void
    {
        $user = User::factory()->create();
        Warehouse::factory()->create(['code' => '100']);

        $response = $this->actingAs($user)->get('/reports/purchase-orders?select=c_100');
        $response->assertOk();
    }

    #[Test]
    public function product_totals_report_handles_numeric_code(): void
    {
        $user = User::factory()->create();
        Warehouse::factory()->create(['code' => '100']);

        $response = $this->actingAs($user)->get('/reports/products-inventory?select=c_100');
        $response->assertOk();
    }
}
