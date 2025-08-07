<?php

namespace Tests\Feature\Reports;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Warehouse;
use App\Modules\Reports\src\Models\DataCollectionReport;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataCollectionReportTotalsTest extends TestCase
{
    #[Test]
    public function totals_columns_are_available(): void
    {
        $warehouse = Warehouse::factory()->create();

        $user = User::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'warehouse_id' => $warehouse->id,
            'quantity_scanned' => 5,
        ]);

        $record->update([
            'unit_cost' => 2,
            'unit_sold_price' => 8,
            'unit_full_price' => 10,
            'quantity_scanned' => 5,
            'total_transferred_in' => 15,
            'total_transferred_out' => 5,
        ]);

        $resource = DataCollectionReport::json();
        $data = $resource->toArray(request());

        $result = collect($data['data'])->firstWhere('id', $record->id);

        $this->assertEquals(10.0, $result['total_cost']);
        $this->assertEquals(40.0, $result['total_sold_price']);
        $this->assertEquals(50.0, $result['total_full_price']);
        $this->assertEquals(10.0, $result['total_discount']);
        $this->assertEquals(30.0, $result['total_profit']);
        $this->assertEquals(10.0, $result['total_adjusted_quantity']);
        $this->assertEquals(20.0, $result['total_adjusted_cost']);
        $this->assertEquals(80.0, $result['total_adjusted_sold_price']);
    }
}

