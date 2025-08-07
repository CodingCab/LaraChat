<?php

namespace Tests\Modules\AssemblyProducts;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\AssemblyProducts\src\AssemblyProductsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AssemblyProductsServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
