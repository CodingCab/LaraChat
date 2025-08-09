<?php

namespace Tests\Feature;

use App\Jobs\CopyRepositoryToHotJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class playgroundTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        CopyRepositoryToHotJob::dispatchSync('ShipTown');
    }
}
