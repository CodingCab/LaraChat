<?php

namespace Tests\Feature\Api\Modules\DpdIreland\PrintDpdLabelController;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $this->assertTrue(true, 'Tested in External/DpdIreland/PrintDpdLabelControllerTest.php');
    }
}
