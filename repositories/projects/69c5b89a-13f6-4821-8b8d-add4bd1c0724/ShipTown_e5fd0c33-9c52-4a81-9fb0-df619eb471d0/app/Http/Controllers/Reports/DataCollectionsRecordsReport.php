<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\DataCollectionRecord;
use App\Modules\Reports\src\Models\Report;

class DataCollectionsRecordsReport extends Controller
{
    public function index()
    {
        $report = new Report();
        $report->baseQuery = DataCollectionRecord::query()
            ->leftJoin('data_collections', 'data_collections.id', '=', 'data_collection_records.data_collection_id')
            ->leftJoin('products', 'products.id', '=', 'data_collection_records.product_id')
            ->leftJoin('inventory', 'inventory.id', '=', 'data_collection_records.inventory_id');


        $report->defaultSort = '-data_collection_records.id';

        $report->addField('warehouse_code', 'data_collection_records.warehouse_code', hidden: false);
        $report->addField('data collection name', 'data_collections.name', hidden: false);

        $report->addField('sku', 'products.sku', hidden: false);
        $report->addField('product name', 'products.name', hidden: false);
        $report->addField('product department', 'products.department');
        $report->addField('product category', 'products.category');
        $report->addField('product supplier', 'products.supplier');

        $report->addField('quantity_scanned', 'data_collection_records.quantity_scanned', 'numeric', hidden: false);
        $report->addField('quantity_requested', 'data_collection_records.quantity_requested', 'numeric', hidden: false);
        $report->addField('total_transferred_in', 'data_collection_records.total_transferred_in', 'numeric', hidden: false);
        $report->addField('total_transferred_out', 'data_collection_records.total_transferred_out', 'numeric', hidden: false);

        $report->addField('total_adjusted_quantity', 'data_collection_records.total_adjusted_quantity', 'numeric', hidden: false);
        $report->addField('total_adjusted_cost', 'data_collection_records.total_adjusted_cost', 'numeric', hidden: false);
        $report->addField('total_adjusted_sold_price', 'data_collection_records.total_adjusted_sold_price', 'numeric', hidden: false);


        $report->addField('quantity_balance', 'data_collection_records.quantity_balance', 'numeric', hidden: false);
        $report->addField('quantity to scan', 'data_collection_records.quantity_to_scan', 'numeric', hidden: false);

        $report->addField('unit_cost', 'data_collection_records.unit_cost', 'numeric', hidden: false);
        $report->addField('unit_sold_price', 'data_collection_records.unit_sold_price', 'numeric', hidden: false);
        $report->addField('unit_discount', 'data_collection_records.unit_discount', 'numeric');
        $report->addField('unit_full_price', 'data_collection_records.unit_full_price', 'numeric');
        $report->addField('price_source', 'data_collection_records.price_source', 'numeric');
        $report->addField('sales_tax_code', 'data_collection_records.sales_tax_code', 'numeric');
        $report->addField('unit_tax', 'data_collection_records.unit_tax', 'numeric');
        $report->addField('comment', 'comment');
        $report->addField('data_collection_type', 'data_collections.type');

        $report->addField('warehouse_id', 'data_collection_records.warehouse_id', 'numeric');
        $report->addField('id', 'data_collection_records.id', 'numeric');
        $report->addField('data_collection_id', 'data_collection_records.data_collection_id', 'numeric');
        $report->addField('inventory_id', 'data_collection_records.inventory_id', 'numeric');
        $report->addField('product_id', 'data_collection_records.product_id', 'numeric');
        $report->addField('price_source_id', 'data_collection_records.price_source_id', 'numeric');
        $report->addField('custom_uuid', 'data_collection_records.custom_uuid');

        return $report->response();
    }
}
