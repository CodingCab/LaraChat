<?php

namespace Tests\Feature\Api\Modules\ScheduledReports;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/modules/scheduled-reports';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'name' => 'Sample report scheduled',
            'uri' => '/reports/inventory-movements?filter%5Bwarehouse_code%5D=DUB&sort=-occurred_at,-sequence_number&filter%5Boccurred_at_between%5D=7%20days%20ago,now',
            'email' => 'sample@example.com',
            'cron' => '0 1 * * *', // every day at 01
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'uri',
                'email',
                'cron',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
