<?php

namespace Tests\Modules\Maintenance\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Modules\Maintenance\src\Jobs\Products\FixQuantityAvailableJob;
use Tests\TestCase;

class FixQuantityAvailableJobTest extends TestCase
{
    #[Test]
    public function testExample(): void
    {
        Product::factory()->count(10)->create();

        Product::query()->update([
            'quantity_available' => 2,
        ]);

        FixQuantityAvailableJob::dispatch();

        $this->assertDatabaseMissing('products', [
            'quantity_available' => 2,
        ]);
    }
}
