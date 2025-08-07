<?php

namespace Tests\Modules\_SampleModuleStructure;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        $this->assertTrue(true, 'This is only samples to copy from');
    }
}
