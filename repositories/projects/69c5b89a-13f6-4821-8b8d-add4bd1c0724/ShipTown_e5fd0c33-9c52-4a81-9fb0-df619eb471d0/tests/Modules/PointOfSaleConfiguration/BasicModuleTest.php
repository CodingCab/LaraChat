<?php

namespace Tests\Modules\PointOfSaleConfiguration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\PointOfSaleConfiguration\src\PointOfSaleConfigurationServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PointOfSaleConfigurationServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->assertDatabaseHas('modules_point_of_sale_configuration', [
            'id' => 1
        ]);
    }
}
