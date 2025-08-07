<?php

namespace Tests\Feature\Settings;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AutomationsDownloadTest extends TestCase
{
    #[Test]
    public function csv_download_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/settings/automations?filename=report.csv');

        $response->assertSuccessful();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
