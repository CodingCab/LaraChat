<?php

namespace App\Modules\Magento2API\InventorySync\src\Reports;

use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiProduct;
use App\Modules\Reports\src\Models\Report;
use Spatie\QueryBuilder\AllowedFilter;

class InventorySourceItemsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->baseQuery = Magento2msiProduct::query();

        $this->addField('connection_id', 'connection_id', hidden: false);
        $this->addField('magento_product_id', 'magento_product_id', hidden: false);
        $this->addField('magento_product_type', 'magento_product_type', hidden: false);
        $this->addField('inventory_totals_by_warehouse_tag_id', 'inventory_totals_by_warehouse_tag_id', hidden: false);
        $this->addField('sync_required', 'sync_required', hidden: false);
        $this->addField('custom_uuid', 'custom_uuid', hidden: false);
        $this->addField('sku', 'sku', hidden: false);
        $this->addField('source_code', 'source_code', hidden: false);
        $this->addField('quantity', 'quantity', hidden: false);
        $this->addField('status', 'status', hidden: false);
        $this->addField('inventory_source_items_fetched_at', 'inventory_source_items_fetched_at', hidden: false);
        $this->addField('inventory_source_items', 'inventory_source_items', 'json', hidden: false);
        $this->addField('created_at', 'created_at', hidden: false);
        $this->addField('updated_at', 'updated_at', hidden: false);

        $this
            ->addFilter(AllowedFilter::exact('sku', 'sku'))
            ->addFilter(AllowedFilter::exact('product_sku', 'sku'))
            ->addFilter(
                AllowedFilter::callback('search_contains', function ($query, $value) {
                    $query->whereLike('sku', "$value%");
                })
            );
    }
}
