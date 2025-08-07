<?php

namespace App\Modules\Reports\src\Models;

use App\Models\DataCollectionPurchaseOrder;
use App\Models\DataCollectionRecord;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class PurchaseOrderReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Purchase Orders';

        $this->baseQuery = DataCollectionRecord::query()
            ->leftJoin('data_collections as purchase_order', 'data_collection_records.data_collection_id', '=', 'purchase_order.id')
            ->leftJoin('products as product', 'data_collection_records.product_id', '=', 'product.id')
            ->where('purchase_order.type', '=', DataCollectionPurchaseOrder::class)
            ->groupBy(DB::raw('product.sku, purchase_order.name'));

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->addField('Purchase Order', 'purchase_order.name', hidden: false);
        $this->addField('SKU', 'product.sku', hidden: false);
        $this->addField('Name', DB::raw('max(product.name)'), hidden: false);
        $this->addField('Department', DB::raw('max(product.department)'));
        $this->addField('Supplier', DB::raw('max(product.supplier)'));

        $this->addField('Quantity', DB::raw('sum(data_collection_records.quantity_requested)'), 'numeric', hidden: false);

        Warehouse::query()->orderBy('code')->each(function ($warehouse) {
            $this->addField("{$warehouse->code}", DB::raw("sum(case when data_collection_records.warehouse_code = '{$warehouse->code}' then data_collection_records.quantity_requested else 0 end)"), 'numeric', hidden: false);
        });

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
