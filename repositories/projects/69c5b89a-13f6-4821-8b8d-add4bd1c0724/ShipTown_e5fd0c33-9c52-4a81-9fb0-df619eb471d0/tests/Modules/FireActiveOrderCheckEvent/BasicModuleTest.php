<?php

namespace Tests\Modules\FireActiveOrderCheckEvent;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        $this->assertTrue(true, 'FireActiveOrderCheckEvent module should be deleted');
    }
}
