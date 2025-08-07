<?php

namespace Tests\Modules\DataCollectorPayments;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorPayments\src\DataCollectorPaymentsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorPaymentsServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
