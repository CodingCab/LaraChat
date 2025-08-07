<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Warehouse;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;

class CsvProductImportReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'CSV Product Imports';
        $this->baseQuery = CsvProductImport::query();

        $this->addField('ID', 'modules_csv_product_imports.id', hidden: false);
        $this->addField('Processed At', 'modules_csv_product_imports.processed_at', 'datetime', hidden: false);
        $this->addField('SKU', 'modules_csv_product_imports.sku', hidden: false);
        $this->addField('Name', 'modules_csv_product_imports.name', hidden: false);
        $this->addField('Department', 'modules_csv_product_imports.department', hidden: false);
        $this->addField('Category', 'modules_csv_product_imports.category', hidden: false);
        $this->addField('Weight', 'modules_csv_product_imports.weight', 'numeric', hidden: false);
        $this->addField('Length', 'modules_csv_product_imports.length', 'numeric', hidden: false);
        $this->addField('Height', 'modules_csv_product_imports.height', 'numeric', hidden: false);
        $this->addField('Width', 'modules_csv_product_imports.width', 'numeric', hidden: false);
        $this->addField('Alias', 'modules_csv_product_imports.alias', hidden: false);
        $this->addField('Tags Add', 'modules_csv_product_imports.tags_add', hidden: false);
        $this->addField('Tags Remove', 'modules_csv_product_imports.tags_remove', hidden: false);
        $this->addField('Price', 'modules_csv_product_imports.price', 'numeric', hidden: false);
        $this->addField('Sale Price', 'modules_csv_product_imports.sale_price', 'numeric', hidden: false);
        $this->addField('Sale Price Start Date', 'modules_csv_product_imports.sale_price_start_date', 'datetime', hidden: false);
        $this->addField('Sale Price End Date', 'modules_csv_product_imports.sale_price_end_date', 'datetime', hidden: false);
        $this->addField('Commodity Code', 'modules_csv_product_imports.commodity_code', hidden: false);
        $this->addField('Sales Tax Code', 'modules_csv_product_imports.sales_tax_code', hidden: false);
        $this->addField('Supplier', 'modules_csv_product_imports.supplier', hidden: false);
        $this->addField('Created At', 'modules_csv_product_imports.created_at', 'datetime');
        $this->addField('Updated At', 'modules_csv_product_imports.updated_at', 'datetime');

        $warehouseCodes = Warehouse::pluck('code');
        foreach ($warehouseCodes as $code) {
            $this->addField("Price $code", "modules_csv_product_imports.price_$code", 'numeric');
            $this->addField("Sale Price $code", "modules_csv_product_imports.sale_price_$code", 'numeric');
            $this->addField("Sale Price Start Date $code", "modules_csv_product_imports.sale_price_start_date_$code", 'datetime');
            $this->addField("Sale Price End Date $code", "modules_csv_product_imports.sale_price_end_date_$code", 'datetime');
            $this->addField("Restock Level $code", "modules_csv_product_imports.restock_level_$code", 'numeric');
            $this->addField("Reorder Point $code", "modules_csv_product_imports.reorder_point_$code", 'numeric');
            $this->addField("Shelve Location $code", "modules_csv_product_imports.shelve_location_$code");
        }
    }
}
