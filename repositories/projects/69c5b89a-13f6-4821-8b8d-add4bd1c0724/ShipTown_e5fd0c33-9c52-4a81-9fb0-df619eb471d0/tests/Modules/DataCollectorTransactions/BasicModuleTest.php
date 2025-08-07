<?php

namespace Tests\Modules\DataCollectorTransactions;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorTransactions\src\DataCollectorTransactionsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorTransactionsServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
