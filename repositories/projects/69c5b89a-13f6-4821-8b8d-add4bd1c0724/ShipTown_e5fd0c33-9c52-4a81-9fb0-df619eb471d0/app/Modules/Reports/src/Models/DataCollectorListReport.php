<?php

namespace App\Modules\Reports\src\Models;

use App\Models\DataCollection;
use App\Models\DataCollectionOfflineInventory;
use App\Models\DataCollectionPurchaseOrder;
use App\Models\DataCollectionStocktake;
use App\Models\DataCollectionTransferIn;
use App\Models\DataCollectionTransferOut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class DataCollectorListReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Data Collector List';

        $this->baseQuery = DataCollection::query()
            ->leftJoin('warehouses', 'warehouses.id', '=', 'data_collections.warehouse_id');

        $differences_count_subquery = DB::raw('(
                SELECT count(*)
                FROM data_collection_records as dcr
                WHERE dcr.data_collection_id = data_collections.id
                AND IFNULL(dcr.quantity_requested, 0) != (dcr.total_transferred_in + dcr.total_transferred_out)
            )');

        $this->addField('ID', 'data_collections.id', 'numeric', hidden: false);
        $this->addField('Warehouse Code', 'warehouses.code', hidden: false);
        $this->addField('Warehouse Name', 'warehouses.name', hidden: false);
        $this->addField('Warehouse ID', 'warehouse_id', hidden: false);
        $this->addField('Type', 'data_collections.type', hidden: false);
        $this->addField('Name', 'data_collections.name', hidden: false);
        $this->addField('Recount Required', 'data_collections.recount_required', 'numeric', hidden: false);
        $this->addField('Calculated At', 'data_collections.calculated_at', 'datetime', hidden: false);
        $this->addField('Destination Warehouse ID', 'data_collections.destination_warehouse_id', 'numeric', hidden: false);
        $this->addField('Destination Collection ID', 'data_collections.destination_collection_id', 'numeric', hidden: false);
        $this->addField('Shipping Address ID', 'data_collections.shipping_address_id', 'numeric', hidden: false);
        $this->addField('Billing Address ID', 'data_collections.billing_address_id', 'numeric', hidden: false);
        $this->addField('Total Quantity Scanned', 'data_collections.total_quantity_scanned', 'numeric', hidden: false);
        $this->addField('Total Cost', 'data_collections.total_cost', 'numeric', hidden: false);
        $this->addField('Total Full Price', 'data_collections.total_full_price', 'numeric', hidden: false);
        $this->addField('Total Discount', 'data_collections.total_discount', 'numeric', hidden: false);
        $this->addField('Total Sold Price', 'data_collections.total_sold_price', 'numeric', hidden: false);
        $this->addField('Total Tax', 'data_collections.total_tax', 'numeric', hidden: false);
        $this->addField('Total Profit', 'data_collections.total_profit', 'numeric', hidden: false);
        $this->addField('Total Paid', 'data_collections.total_paid', 'numeric', hidden: false);
        $this->addField('Total Outstanding', 'data_collections.total_outstanding', 'numeric', hidden: false);
        $this->addField('Custom UUID', 'data_collections.custom_uuid', hidden: false);
        $this->addField('Differences Count', $differences_count_subquery, 'numeric', hidden: false);
        $this->addField('Created At', 'data_collections.created_at', 'datetime', hidden: false);
        $this->addField('Updated At', 'data_collections.updated_at', 'datetime', hidden: false);
        $this->addField('Deleted At', 'data_collections.deleted_at', 'datetime', hidden: false);
        $this->addField('Currently Running Task', 'data_collections.currently_running_task', hidden: false);

        $this->addFilter(
            AllowedFilter::callback('with_archived', function ($query, $value) {
                if ($value === true) {
                    $query->withTrashed();
                }
            })
        );

        $this->addFilter(
            AllowedFilter::callback('only_archived', function ($query, $value) {
                if ($value === true) {
                    $query->onlyTrashed();
                }
            })
        );

        $this->addFilter(
            AllowedFilter::callback('without_transactions', function (Builder $query, $value) {
                if ($value === true) {
                    $query->where(function (Builder $query) {
                        $query->whereIn('data_collections.type', [
                            DataCollectionTransferIn::class,
                            DataCollectionTransferOut::class,
                            DataCollectionOfflineInventory::class,
                            DataCollectionStocktake::class,
                            DataCollectionPurchaseOrder::class,
                        ])
                            ->orWhereNull('data_collections.type');
                    });
                }
            })
        );

        $this->addAllowedInclude('comments');
        $this->addAllowedInclude('comments.user');
        $this->addAllowedInclude('shippingAddress');
        $this->addAllowedInclude('billingAddress');
        $this->addAllowedInclude('payments');
    }
}
