<?php

namespace App\Modules\CsvProductImports\src\Models;

use App\BaseModel;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * CSV Product Import Model
 *
 * @property int $id
 * @property int $file_id
 * @property int|null $line_number
 * @property Carbon|null $processed_at
 * @property int|null $product_id
 * @property string|null $sku
 * @property string|null $name
 * @property string|null $department
 * @property string|null $category
 * @property float|null $weight
 * @property float|null $length
 * @property float|null $height
 * @property float|null $width
 * @property int|null $pack_quantity
 * @property string|null $alias
 * @property string|null $tags_add
 * @property string|null $tags_remove
 * @property float|null $price
 * @property float|null $sale_price
 * @property Carbon|null $sale_price_start_date
 * @property Carbon|null $sale_price_end_date
 * @property string|null $commodity_code
 * @property string|null $sales_tax_code
 * @property string|null $supplier
 * @property string|null $supplier_code
 *
 * Additionally, for each warehouse code:
 * @property float|null $price_[warehouse_code]
 * @property float|null $sale_price_[warehouse_code]
 * @property Carbon|null $sale_price_start_date_[warehouse_code]
 * @property Carbon|null $sale_price_end_date_[warehouse_code]
 * @property float|null $restock_level_[warehouse_code]
 * @property float|null $reorder_point_[warehouse_code]
 * @property string|null $shelve_location_[warehouse_code]
 *
 * @property Product|null $product
 *
 */
class CsvProductImport extends BaseModel
{
    use HasFactory;

    protected $table = 'modules_csv_product_imports';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'file_id' => 'integer',
            'line_number' => 'integer',
            'sale_price_start_date' => 'datetime',
            'sale_price_end_date' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
