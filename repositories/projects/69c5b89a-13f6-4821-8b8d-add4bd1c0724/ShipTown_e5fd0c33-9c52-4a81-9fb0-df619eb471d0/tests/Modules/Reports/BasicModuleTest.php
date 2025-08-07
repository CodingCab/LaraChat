<?php

namespace Tests\Modules\Reports;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}
