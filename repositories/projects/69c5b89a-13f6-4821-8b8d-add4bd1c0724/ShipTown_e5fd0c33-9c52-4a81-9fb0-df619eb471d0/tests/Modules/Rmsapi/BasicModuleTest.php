<?php

namespace Tests\Modules\Rmsapi;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Rmsapi\src\RmsapiModuleServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        RmsapiModuleServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}
