<?php

namespace App\Modules\AssemblyProducts\src\Reports;

use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class AssemblyProductsElementsReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Assembly Products Elements');

        $this->baseQuery = AssemblyProductsElement::query()
            ->leftJoin(
                'products as assembly_product',
                'assembly_products_elements.assembly_product_id',
                '=',
                'assembly_product.id'
            )
            ->leftJoin(
                'products as simple_product',
                'assembly_products_elements.simple_product_id',
                '=',
                'simple_product.id'
            )
            ->leftJoin(
                'inventory',
                'assembly_products_elements.simple_product_id',
                '=',
                'inventory.product_id'
            );

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('assembly_product.sku', "$value%")
                    ->orWhereLike('simple_product.sku', "$value%");
            })
        );

        $this->addField('ID', 'assembly_products_elements.id', 'numeric', hidden: false);
        $this->addField('Assembly Product SKU', 'assembly_product.sku', hidden: false);
        $this->addField('Simple Product SKU', 'simple_product.sku', hidden: false);
        $this->addField('Required Quantity', 'assembly_products_elements.required_quantity', 'numeric', hidden: false);
        $this->addField('Warehouse Code', 'inventory.warehouse_code', hidden: false);
        $this->addField('Quantity', 'inventory.quantity', 'numeric', hidden: false);
        $this->addField('Assemblies Possible', DB::raw('
            CASE
                WHEN inventory.quantity IS NULL OR inventory.quantity = 0 THEN 0
                ELSE FLOOR(inventory.quantity / assembly_products_elements.required_quantity)
            END
        '), 'numeric', hidden: false);
        $this->addField('Assembly Product Name', 'assembly_product.name');
        $this->addField('Simple Product Name', 'simple_product.name');
        $this->addField('Assembly Product ID', 'assembly_products_elements.assembly_product_id', 'numeric');
        $this->addField('Simple Product ID', 'assembly_products_elements.simple_product_id', 'numeric');
        $this->addField('Created At', 'assembly_products_elements.created_at', 'datetime');
        $this->addField('Updated At', 'assembly_products_elements.updated_at', 'datetime');
        $this->addField('Deleted At', 'assembly_products_elements.deleted_at', 'datetime');
    }
}
