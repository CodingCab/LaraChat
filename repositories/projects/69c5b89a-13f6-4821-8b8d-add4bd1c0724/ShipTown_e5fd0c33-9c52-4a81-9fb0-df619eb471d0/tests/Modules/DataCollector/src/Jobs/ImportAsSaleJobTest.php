<?php

namespace Tests\Modules\DataCollector\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;
use App\Models\DataCollection;

class ImportAsSaleJobTest extends JobTestAbstract
{
    public function test_job()
   {
        $dataCollection = DataCollection::factory()->create();

        App\Modules\DataCollector\src\Jobs\ImportAsSaleJob::dispatchSync($dataCollection->getKey());

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
