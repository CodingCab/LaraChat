<?php

namespace Tests\Modules\SalesTaxes;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Modules\SalesTaxes\src\SalesTaxesModuleServiceProvider;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SalesTaxesModuleServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
