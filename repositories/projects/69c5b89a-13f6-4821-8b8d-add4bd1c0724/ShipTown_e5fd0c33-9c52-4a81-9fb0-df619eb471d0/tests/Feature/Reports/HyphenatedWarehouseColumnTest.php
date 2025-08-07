<?php

namespace Tests\Feature\Reports;

use App\Models\Warehouse;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HyphenatedWarehouseColumnTest extends TestCase
{
    #[Test]
    public function purchase_orders_report_handles_hyphenated_code(): void
    {
        $user = User::factory()->create();
        Warehouse::factory()->create(['code' => 'WH-1']);

        $response = $this->actingAs($user)->get('/reports/purchase-orders?select=wh-1');
        $response->assertOk();
    }

    #[Test]
    public function product_totals_report_handles_hyphenated_code(): void
    {
        $user = User::factory()->create();
        Warehouse::factory()->create(['code' => 'WH-1']);

        $response = $this->actingAs($user)->get('/reports/products-inventory?select=wh-1');
        $response->assertOk();
    }
}
