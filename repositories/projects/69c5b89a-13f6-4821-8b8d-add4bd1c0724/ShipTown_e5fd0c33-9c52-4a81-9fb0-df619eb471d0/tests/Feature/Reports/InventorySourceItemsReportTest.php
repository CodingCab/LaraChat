<?php

namespace Tests\Feature\Reports;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InventorySourceItemsReportTest extends TestCase
{
    private string $uri = '/settings/modules/magento2api/inventory-source-items';

    #[Test]
    public function test_index_returns_ok(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get($this->uri);

        $response->assertOk();
    }
}
