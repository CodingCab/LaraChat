<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CopyRepositoryToHotJob;
use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyRepositoryToHotJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $repository = Repository::factory()->create();
        $job = new CopyRepositoryToHotJob($repository);
        
        $this->assertInstanceOf(CopyRepositoryToHotJob::class, $job);
    }
}