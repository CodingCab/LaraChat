<?php

namespace App\Models;

use App\Helpers\HasQuantityRequiredSort;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\SalesTaxes\src\Models\SaleTax;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property int $data_collection_id,
 * @property int $inventory_id
 * @property int $product_id
 * @property string $warehouse_code
 * @property int $warehouse_id
 * @property float $total_transferred_in
 * @property float $total_transferred_out
 * @property float $total_adjusted_quantity
 * @property float $total_adjusted_cost
 * @property float $total_adjusted_sold_price
 * @property float $quantity_requested
 * @property float $quantity_scanned
 * @property float $quantity_to_scan
 * @property float $unit_cost
 * @property float $unit_sold_price
 * @property float $unit_discount
 * @property float $unit_full_price
 * @property string $price_source
 * @property int $price_source_id
 * @property float $total_cost_price
 * @property float $total_sold_price
 * @property float $total_full_price
 * @property float $total_discount
 * @property float $total_price
 * @property string $sales_tax_code
 * @property float $unit_tax
 * @property float $calculated_unit_tax
 * @property float $total_tax
 * @property float $calculated_total_tax
 * @property bool $recalculate_unit_tax
 * @property string $custom_uuid
 * @property string $comment
 * @property bool $is_scanned
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder|Product skuOrAlias($skuOrAlias)
 *
 * @property Product $product
 * @property DataCollection $dataCollection
 * @property Inventory $inventory
 * @property ProductPrice $prices
 * @property QuantityDiscount $discount
 */
class DataCollectionRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'data_collection_id',
        'inventory_id',
        'product_id',
        'warehouse_code',
        'warehouse_id',
        'is_processed',
        'is_reserved',
        'total_transferred_in',
        'total_transferred_out',
        'quantity_requested',
        'quantity_scanned',
        'unit_cost',
        'unit_sold_price',
        'unit_discount',
        'unit_full_price',
        'price_source',
        'price_source_id',
        'custom_uuid',
        'sales_tax_code',
        'unit_tax',
        'calculated_unit_tax',
        'recalculate_unit_tax',
        'comment',
    ];

    protected $guarded = [
        'total_cost',
        'total_tax',
        'calculated_total_tax'
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'int',
            'product_id' => 'int',
            'total_transferred_in' => 'double',
            'total_transferred_out' => 'double',
            'total_adjusted_quantity' => 'double',
            'total_adjusted_cost' => 'double',
            'total_adjusted_sold_price' => 'double',
            'quantity_requested' => 'double',
            'quantity_scanned' => 'double',
            'quantity_to_scan' => 'double',
            'unit_cost' => 'float',
            'unit_sold_price' => 'float',
            'unit_discount' => 'float',
            'unit_full_price' => 'float',
            'price_source' => 'string',
            'price_source_id' => 'int',
            'total_discount' => 'float',
            'total_cost' => 'float',
            'total_price' => 'float',
            'total_sold_price' => 'float',
            'total_full_price' => 'float',
            'total_cost_price' => 'float',
            'total_profit' => 'float',
            'unit_tax' => 'float',
            'calculated_unit_tax' => 'float',
            'total_tax' => 'float',
            'calculated_total_tax' => 'float',
            'recalculate_unit_tax' => 'boolean',
        ];
    }

    public function replicate(?array $except = null): self
    {
        // these are computed columns or columns that should not be copied when replicating a record
        return parent::replicate(array_merge($except ?? [], [
            'quantity_to_scan',
            'unit_discount',
            'total_discount',
            'total_transferred_in',
            'total_transferred_out',
            'total_adjusted_quantity',
            'total_adjusted_cost',
            'total_adjusted_sold_price',
            'total_sold_price',
            'total_full_price',
            'total_cost_price',
            'total_profit',
            'total_cost',
            'total_price',
            'total_tax',
            'quantity_balance',
            'calculated_total_tax',
            'is_requested',
            'is_fully_scanned',
            'is_over_scanned',
        ]));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DataCollectionRecord::class, 'parent_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function dataCollection(): BelongsTo
    {
        return $this->belongsTo(DataCollection::class)->withTrashed();
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function prices(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'inventory_id', 'inventory_id');
    }

    public function discount(): HasOne
    {
        return $this->hasOne(QuantityDiscount::class, 'id', 'price_source_id');
    }

    public function saleTax(): HasOne
    {
        return $this->hasOne(SaleTax::class, 'code', 'sales_tax_code');
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        $allowedSort = AllowedSort::custom('has_quantity_required', new HasQuantityRequiredSort);

        return QueryBuilder::for(DataCollectionRecord::class)
            ->allowedFilters([])
            ->allowedSorts([
                'id',
                'quantity_requested',
                'quantity_scanned',
                'quantity_to_scan',
                'updated_at',
                'created_at',
            ])
            ->allowedIncludes([
                'product',
                'inventory',
                'product.inventory',
                'product.user_inventory',
                'dataCollection',
            ])
            ->defaultSort($allowedSort, '-updated_at');
    }

    public function scopeSkuOrAlias($query, string $value)
    {
        $query->where(function ($query) use ($value) {
            return $query
                ->whereIn('data_collection_records.product_id', ProductAlias::query()
                    ->select('products_aliases.product_id')
                    ->where('alias', $value));
        });

        return $query;
    }
}
