<?php

namespace Tests\Modules\PrintNode;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\PrintNode\src\PrintNodeServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        PrintNodeServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}
