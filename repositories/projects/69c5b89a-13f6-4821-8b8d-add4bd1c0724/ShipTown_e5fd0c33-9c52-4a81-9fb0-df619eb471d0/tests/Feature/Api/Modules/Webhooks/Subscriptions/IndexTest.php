<?php

namespace Tests\Feature\Api\Modules\Webhooks\Subscriptions;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $this->assertTrue(true, 'Tested in External/Webhooks');
    }
}
