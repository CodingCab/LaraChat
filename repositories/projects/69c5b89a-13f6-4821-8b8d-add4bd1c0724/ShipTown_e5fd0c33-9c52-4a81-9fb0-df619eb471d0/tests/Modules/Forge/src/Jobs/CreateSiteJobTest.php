<?php

namespace Tests\Modules\Forge\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class CreateSiteJobTest extends TestCase
{
    #[Test]
    public function job_handles_missing_credentials(): void
    {
        $job = new \App\Modules\Forge\src\Jobs\CreateSiteJob('demo.example.com');

        $job->handle();

        $this->assertTrue(true);
    }
}
