<?php

namespace Tests\Modules\DataCollector\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Modules\DataCollector\src\Jobs\ImportAsStocktakeJob;
use Tests\TestCase;

class ImportAsStocktakeJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        $dataCollection = DataCollection::factory()->create();

        $job = new ImportAsStocktakeJob($dataCollection->getKey());
        $job->handle();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
