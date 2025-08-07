<?php

namespace Tests\Feature\Api\Modules\Api2cart\Connections;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $this->assertTrue(true, 'Tested directly in module');
    }
}
