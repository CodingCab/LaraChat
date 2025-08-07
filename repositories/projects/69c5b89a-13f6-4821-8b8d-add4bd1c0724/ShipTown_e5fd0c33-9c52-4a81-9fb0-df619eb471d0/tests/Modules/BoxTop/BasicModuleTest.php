<?php

namespace Tests\Modules\BoxTop;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\BoxTop\src\BoxTopServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        BoxTopServiceProvider::enableModule();

        $this->assertTrue(true, 'Make sure no exceptions when enabling');
    }
}
