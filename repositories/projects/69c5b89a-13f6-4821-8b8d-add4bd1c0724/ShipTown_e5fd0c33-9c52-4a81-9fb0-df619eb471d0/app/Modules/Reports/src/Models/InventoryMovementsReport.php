<?php

namespace App\Modules\Reports\src\Models;

use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryMovementsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Movements Report';
        $this->defaultSort = '-occurred_at';

        $this->baseQuery = InventoryMovement::query()
            ->leftJoin('products', 'products.id', '=', 'inventory_movements.product_id');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('products.sku', "$value%");
            })
        );

        $this->addFilter(
            AllowedFilter::callback('occurred_at_between', function ($query, $value) {
                if ((! is_array($value)) || count($value) !== 2) {
                    $query->whereRaw('1=2');

                    return;
                }

                // we are using datestamp for performance reasons
                // integer is way faster than datetime for filtering
                $fromDate = (integer) Carbon::parse($value[0])->format('Ymd');
                $toDate = (integer) Carbon::parse($value[1])->format('Ymd');
                $query->whereBetween('inventory_movements.occurred_at_datestamp', [$fromDate, $toDate]);

                $fromDateTime = Carbon::parse($value[0])->toDateTimeString();
                $toDateTime = Carbon::parse($value[1])->toDateTimeString();
                $query->whereBetween('inventory_movements.occurred_at', [$fromDateTime, $toDateTime]);
            })
        );

        $this->allowedIncludes = [
            'inventory',
            'product',
            'warehouse',
            'user',
            'product.tags',
            'product.productDescriptions',
            'product.productPicture',
            'product.saleTax',
        ];

        $this->addField('ID', 'inventory_movements.id', 'integer', hidden: false);
        $this->addField('Warehouse Code', 'inventory_movements.warehouse_code', hidden: false);
        $this->addField('Occurred At', 'inventory_movements.occurred_at', 'datetime', hidden: false, filterable: false);
        $this->addField('Product SKU', 'products.sku', hidden: false);
        $this->addField('Product Name', 'products.name', hidden: false);
        $this->addField('Department', 'products.department', hidden: false);
        $this->addField('Category', 'products.category');

        $this->addField('Type', 'inventory_movements.type', hidden: false);

        $this->addField('Percentage Change', DB::raw('IF(inventory_movements.quantity_before = 0, 100.00, ROUND(-100+(inventory_movements.quantity_after / inventory_movements.quantity_before * 100), 2))'), 'numeric');
        $this->addField('Unit Cost', 'inventory_movements.unit_cost', 'numeric');
        $this->addField('Unit Price', 'inventory_movements.unit_price', 'numeric', hidden: false);

        $this->addField('Quantity Before', 'inventory_movements.quantity_before', 'numeric');
        $this->addField('Quantity Delta', 'inventory_movements.quantity_delta', 'numeric', hidden: false);
        $this->addField('Quantity After', 'inventory_movements.quantity_after', 'numeric');
        $this->addField('Total Cost', 'inventory_movements.total_cost', 'numeric');
        $this->addField('Total Price', 'inventory_movements.total_price', 'numeric', hidden: false);
        $this->addField('Description', 'inventory_movements.description', hidden: false);
        $this->addField('Sequence Number', 'inventory_movements.sequence_number', 'numeric');
        $this->addField('ID', 'inventory_movements.id', 'numeric');
        $this->addField('User ID', 'inventory_movements.user_id', 'numeric');
        $this->addField('Product ID', 'inventory_movements.product_id', 'numeric');
        $this->addField('Inventory ID', 'inventory_movements.inventory_id', 'numeric');
        $this->addField('Warehouse ID', 'inventory_movements.warehouse_id', 'numeric');
        $this->addField('Sales Tax Code', 'inventory_movements.sales_tax_code');
        $this->addField('Unit Tax', 'inventory_movements.unit_tax', 'numeric');
        $this->addField('Total Tax', 'inventory_movements.total_tax', 'numeric');
        $this->addField('Updated At', 'inventory_movements.updated_at', 'datetime');
        $this->addField('Created At', 'inventory_movements.created_at', 'datetime');
        $this->addField('Custom Unique Reference ID', 'inventory_movements.custom_unique_reference_id');

        $this->addFilter(
            AllowedFilter::callback('product_has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->hasTags($value);
                });
            })
        );

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );

        $this->addFilter(
            AllowedFilter::callback('search', function ($query, $value) {
                $query->whereHas('alias', function ($query) use ($value) {
                    $query->where(['alias' => $value]);
                });
            })
        );
    }
}
