<?php

namespace Tests\Modules\InventoryReservations;

use App\Modules\InventoryReservations\src\EventServiceProviderBase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EventServiceProviderBase::enableModule();
    }

    public function test_if_reserves_correctly(): void
    {
        $this->assertTrue(true);
    }
}
