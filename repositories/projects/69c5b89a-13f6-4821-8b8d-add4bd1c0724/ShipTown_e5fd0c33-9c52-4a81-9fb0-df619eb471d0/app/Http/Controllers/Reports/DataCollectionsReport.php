<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\DataCollection;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Support\Facades\DB;

class DataCollectionsReport extends Controller
{
    public function index()
    {
        $report = new Report();
        $report->baseQuery = DataCollection::withTrashed();

        $report->defaultSort = '-data_collections.created_at';

        $report->addField('created_at', 'created_at', 'datetime', hidden: false);
        $report->addField('name', 'name', hidden: false);
        $report->addField('type', DB::raw('REPLACE(type, "App\\\\Models\\\\DataCollection", "")'), hidden: false);
        $report->addField('warehouse_code', 'warehouse_code', hidden: false);
        $report->addField('destination_warehouse_code', 'destination_warehouse_code');
        $report->addField('total_quantity_scanned', 'total_quantity_scanned', 'numeric', hidden: false);
        $report->addField('total cost', 'total_cost', 'numeric', hidden: false);
        $report->addField('total retail value', 'total_full_price', 'numeric', hidden: false);
        $report->addField('total_discount', 'total_discount', 'numeric', hidden: false);
        $report->addField('total retail value', 'total_sold_price', 'numeric', hidden: false);
        $report->addField('total_tax', 'total_tax', 'numeric', hidden: false);
        $report->addField('total_paid', 'total_paid', 'numeric', hidden: false);
        $report->addField('total_outstanding', 'total_outstanding', 'numeric', hidden: false);
        $report->addField('total_profit', 'total_profit', 'numeric', hidden: false);

        $report->addField('updated_at', 'updated_at');
        $report->addField('archived_at', 'deleted_at');
        $report->addField('custom_uuid', 'custom_uuid');

        $report->addField('id', 'id');
        $report->addField('warehouse_id', 'warehouse_id');
        $report->addField('destination_warehouse_id', 'destination_warehouse_id', 'numeric');
        $report->addField('destination_collection_id', 'destination_collection_id', 'numeric');
        $report->addField('shipping_address_id', 'shipping_address_id');
        $report->addField('billing_address_id', 'billing_address_id');

        return $report->response();
    }
}
