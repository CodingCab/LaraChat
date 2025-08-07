<?php

namespace App\Modules\Magento2API\InventorySync\src\Reports;

use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiProduct;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Support\Facades\DB;

class TotalsByConnectionReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->baseQuery = Magento2msiProduct::query()
            ->groupBy('connection_id');

        $this->fields = [
            'connection_id' => 'connection_id',
            'record_count' => DB::raw('count(*)'),
            'exists_in_magento_null' => DB::raw('count(IF(exists_in_magento IS NULL, 1, NULL))'),
            'exists_in_magento_true' => DB::raw('count(IF(exists_in_magento = 1, 1, NULL))'),
            'exists_in_magento_false' => DB::raw('count(IF(exists_in_magento = 0, 1, NULL))'),
            'source_assigned_null' => DB::raw('count(IF(source_assigned IS NULL, 1, NULL))'),
            'source_assigned_true' => DB::raw('count(IF(source_assigned = 1, 1, NULL))'),
            'source_assigned_false' => DB::raw('count(IF(source_assigned = 0, 1, NULL))'),
            'sync_required_null' => DB::raw('count(IF(sync_required IS NULL, 1, NULL))'),
            'sync_required_true' => DB::raw('count(IF(sync_required = 1, 1, NULL))'),
            'sync_required_false' => DB::raw('count(IF(sync_required = 0, 1, NULL))'),
        ];

        //SELECT
        //    connection_id,
        //    count(*),
        //    count(IF(exists_in_magento = 0, 1, NULL)) as exists_in_magento_false,
        //    count(IF(source_assigned IS NULL, 1, NULL)) as source_assigned_null,
        //    count(IF(sync_required IS NULL, 1, NULL)) as sync_required_null,
        //    count(IF(inventory_source_items_fetched_at IS NULL, 1, NULL)) as inventory_source_items_fetched_at_null,
        //
        //    count(IF(exists_in_magento IS NULL, 1, NULL)) as exists_in_magento_null,
        //    count(IF(exists_in_magento = 1, 1, NULL)) as exists_in_magento_true,
        //
        //    count(IF(source_assigned = 1, 1, NULL)) as source_assigned_true,
        //    count(IF(source_assigned = 0, 1, NULL)) as source_assigned_false,
        //
        //    count(IF(sync_required = 1, 1, NULL)) as sync_required_true,
        //    count(IF(sync_required = 0, 1, NULL)) as sync_required_false,
        //    count(IF(inventory_source_items_fetched_at IS NOT NULL, 1, NULL)) as inventory_source_items_fetched_at_not_null
        //
        //FROM `modules_magento2msi_inventory_source_items`
        //
        //GROUP BY connection_id
        //
        //LIMIT 10
    }
}
