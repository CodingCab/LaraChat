<?php

namespace Tests\Modules\Forge;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Forge\src\Jobs\CreateSiteJob;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        // We just need to make sure that no exceptions are thrown when we run the module.
        CreateSiteJob::dispatch('demo.products.management.com');

        $this->assertTrue(true);
    }
}
