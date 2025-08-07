<?php

namespace Tests\Modules\CsvProductImports;

use App\Modules\CsvProductImports\src\CsvProductImportsServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        CsvProductImportsServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->assertTrue(true, 'Each job has its own tests');
    }
}
