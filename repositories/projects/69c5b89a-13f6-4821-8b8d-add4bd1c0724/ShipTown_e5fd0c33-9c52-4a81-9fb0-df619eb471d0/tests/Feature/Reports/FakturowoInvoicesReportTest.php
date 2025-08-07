<?php

namespace Tests\Feature\Reports;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FakturowoInvoicesReportTest extends TestCase
{
    #[Test]
    public function test_index_returns_ok_without_filename(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports/fakturowo-invoices');

        $response->assertOk();
    }
}
