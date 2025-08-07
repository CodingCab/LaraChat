<?php

namespace Tests\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Modules\DataCollectorTransfersOutReserveStock\src\DataCollectorTransfersOutReserveStockServiceProvider;
use Illuminate\Support\Facades\DB;

class UpdateStockReservationsJobTest extends JobTestAbstract
{
    public function testIfReservesQuantity()
    {
        DataCollectorTransfersOutReserveStockServiceProvider::enableModule();

        $dataCollection = DataCollection::factory()->create(['type' => DataCollectionTransferOut::class]);

        /** @var DataCollectionRecord $dataCollectionRecord */
        $dataCollectionRecord = DataCollectionRecord::factory()->create(['data_collection_id' => $dataCollection->getKey()]);

        $this->assertEquals($dataCollectionRecord->quantity_scanned, $dataCollectionRecord->inventory->refresh()->quantity_reserved);
    }
    public function testIfUpdatesQuantity()
    {
        DataCollectorTransfersOutReserveStockServiceProvider::enableModule();

        $dataCollection = DataCollection::factory()->create(['type' => DataCollectionTransferOut::class]);

        /** @var DataCollectionRecord $dataCollectionRecord */
        $dataCollectionRecord = DataCollectionRecord::factory()->create(['data_collection_id' => $dataCollection->getKey()]);

        $dataCollectionRecord->update([
            'quantity_scanned' => $dataCollectionRecord->quantity_scanned + 1
        ]);

        $this->assertEquals($dataCollectionRecord->quantity_scanned, $dataCollectionRecord->inventory->refresh()->quantity_reserved);
    }

    public function testIfReleasesQuantity()
    {
        DataCollectorTransfersOutReserveStockServiceProvider::enableModule();

        $dataCollection = DataCollection::factory()->create(['type' => DataCollectionTransferOut::class]);

        /** @var DataCollectionRecord $dataCollectionRecord */
        $dataCollectionRecord = DataCollectionRecord::factory()->create(['data_collection_id' => $dataCollection->getKey()]);

        $dataCollectionRecord->delete();

        $this->assertEquals(0, $dataCollectionRecord->inventory->refresh()->quantity_reserved);
    }
}
