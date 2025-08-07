<?php

namespace App\Models;

use App\BaseModel;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic;
use App\Modules\SalesTaxes\src\Models\SaleTax;
use App\Traits\HasTagsTrait;
use App\Traits\LogsActivityTrait;
use App\User;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Hulkur\HasManyKeyBy\HasManyKeyByRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\SortsCallback;
use Spatie\Tags\Tag;

/**
 * App\Models\Product.
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $supplier
 * @property string|null $supplier_code
 * @property string $type
 * @property float $price
 * @property float $sale_price
 * @property Carbon $sale_price_start_date
 * @property Carbon $sale_price_end_date
 * @property string $commodity_code
 * @property string $default_tax_code
 * @property float $quantity
 * @property float $quantity_reserved
 * @property float $quantity_available
 * @property Collection|Tag[] $tags
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|InventoryTotal[] $inventoryTotals
 * @property-read int|null $inventoryTotals_count
 * @property-read int|null $activities_count
 * @property-read Collection|ProductAlias[] $aliases
 * @property-read SaleTax $saleTax
 * @property-read int|null $aliases_count
 * @property-read Collection|Inventory[] $inventory
 * @property-read int|null $inventory_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $tags_count
 * @property-read Collection|Product[] $assemblyProductsElements
 *
 * @method static Builder|Product addInventorySource($inventory_warehouse_code)
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product skuOrAlias($skuOrAlias)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereHasText($text)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereName($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereQuantity($value)
 * @method static Builder|Product whereQuantityReserved($value)
 * @method static Builder|Product whereSalePrice($value)
 * @method static Builder|Product whereSalePriceEndDate($value)
 * @method static Builder|Product whereSalePriceStartDate($value)
 * @method static Builder|Product whereSku($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @method static Builder|Product withAllTagsOfAnyType($tags)
 * @method static Builder|Product withAnyTags($tags, $type = null)
 * @method static Builder|Product withAnyTagsOfAnyType($tags)
 * @method static Builder|Product withoutAllTags($tags, $type = null)
 *
 * @property-read Collection|ProductPrice[] $prices
 * @property-read int|null $prices_count
 *
 * @method static Builder|Product hasTags($tags)
 * @method static Builder|Product whereQuantityAvailable($value)
 *
 * @mixin Eloquent
 */
class Product extends BaseModel
{
    use HasFactory;
    use HasManyKeyByRelationship;
    use HasTagsTrait;
    use LogsActivityTrait;
    use Notifiable;
    use SoftDeletes;

    protected static array $logAttributes = [
        'quantity',
        'quantity_reserved',
        'quantity_available',
    ];

    protected $fillable = [
        'sku',
        'name',
        'department',
        'category',
        'type',
        'price',
        'sale_price',
        'sale_price_start_date',
        'sale_price_end_date',
        'default_tax_code',
        'quantity_reserved',
        'quantity',
        'supplier',
        'supplier_code',
        'weight',
        'length',
        'width',
        'height',
        'pack_quantity',
        'product_number'
    ];

    // we use attributes to set default values
    // we wont use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'name' => '',
        'price' => 0,
        'sale_price' => 0,
        'sale_price_start_date' => '2001-01-01 00:00:00',
        'sale_price_end_date' => '2001-01-01 00:00:00',
        'quantity' => 0,
        'quantity_reserved' => 0,
        'quantity_available' => 0,
        'pack_quantity' => 1,
        'product_number' => '',
    ];

    protected function casts(): array
    {
        return [
            'sale_price_start_date' => 'datetime',
            'sale_price_end_date' => 'datetime',
            'quantity' => 'float',
            'quantity_reserved' => 'float',
            'quantity_available' => 'float',
        ];
    }

    public function save(array $options = [])
    {
        $quantity_available = $this->quantity - $this->quantity_reserved;

        if ($this->quantity_available != $quantity_available) {
            $this->quantity_available = $quantity_available;
        }

        return parent::save($options);
    }

    public function getQuantityAttribute()
    {
        //        report(new \Exception('Quantity should be accessed via InventoryTotals->quantity'));

        return $this->attributes['quantity'];
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('price'),
                AllowedFilter::scope('inventory_source_warehouse_code', 'addInventorySource')->ignore(['', null]),

                AllowedFilter::scope('search', 'whereHasText'),
                AllowedFilter::scope('has_tags', 'hasTags'),
                AllowedFilter::scope('product_has_tags', 'hasTags'),
                AllowedFilter::scope('without_tags', 'withoutAllTags'),
                AllowedFilter::scope('sku_or_alias', 'skuOrAlias'),
                AllowedFilter::scope('sku_or_alias_is_not', 'skuOrAliasIsNot'),
            ])
            ->allowedSorts([
                'id',
                'sku',
                'name',
                'price',
                'quantity',
                AllowedSort::custom('inventory_totals.quantity', new SortsCallback(function ($query, $descending, $property) {
                    $query->orderByRaw(
                        '(SELECT quantity FROM inventory_totals
                        WHERE inventory_totals.product_id = products.id
                        ORDER BY inventory_totals.updated_at DESC
                        LIMIT 1) ' . ($descending ? 'desc' : 'asc')
                    );
                }))
            ])
            ->allowedIncludes([
                'inventory',
                'user_inventory',
                'aliases',
                'tags',
                'modelTags',
                'prices',
                'inventory.warehouse',
                'inventoryMovementsStatistics',
                'inventoryTotals',
                'productDescriptions',
                'productPicture',
                'saleTax',
                'assemblyProducts',
                'assemblyProducts.simpleProduct',
            ]);
    }

    public function scopeSkuOrAliasIsNot($query, string $value)
    {
        $query->where(function ($query) use ($value) {
            return $query
                ->whereNotIn('id', ProductAlias::query()->select('product_id')->where('alias', $value));
        });

        return $query;
    }

    public function scopeSkuOrAlias($query, string $value)
    {
        $query->where(function ($query) use ($value) {
            return $query
                ->whereIn('id', ProductAlias::query()->select('product_id')->where('alias', $value));
        });

        return $query;
    }

    public function setQuantityReservedAttribute($value)
    {
        $this->attributes['quantity_reserved'] = $value;
        $this->quantity_available = $this->quantity - $this->quantity_reserved;
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
        $this->quantity_available = $this->quantity - $this->quantity_reserved;
    }

    public function reserveStock(float $quantity): Product
    {
        $this->quantity_reserved += $quantity;
        $this->save();

        return $this;
    }

    public function inventoryTotals(): HasMany
    {
        return $this->hasMany(InventoryTotal::class);
    }

    protected function onTagAttached($tag)
    {
        ProductTagAttachedEvent::dispatch($this, $tag);
    }

    protected function onTagDetached($tag)
    {
        ProductTagDetachedEvent::dispatch($this, $tag);
    }

    /**
     * @param  mixed  $query
     */
    public function scopeWhereHasText($query, string $text): mixed
    {
        return $query->where('sku', $text)
            ->orWhereHas('aliases', function (Builder $query) use ($text) {
                return $query->where('alias', '=', $text)
                    ->orWhere('alias', 'like', '%' . $text . '%');
            })
            ->orWhere('sku', 'like', '%' . $text . '%')
            ->orWhere('name', 'like', '%' . $text . '%');
    }

    /**
     * @param  mixed  $query
     * @param  mixed  $inventory_warehouse_code
     * @return mixed
     */
    public function scopeAddInventorySource($query, $inventory_warehouse_code)
    {
        $source_inventory = Inventory::query()
            ->select([
                'shelve_location as inventory_source_shelf_location',
                'quantity as inventory_source_quantity',
                'product_id as inventory_source_product_id',
                'warehouse_id as inventory_source_warehouse_id',
                'warehouse_code as inventory_source_warehouse_code',
            ])
            ->where(['warehouse_code' => $inventory_warehouse_code])
            ->toBase();

        return $query->leftJoinSub($source_inventory, 'inventory_source', function ($join) {
            $join->on('products.id', '=', 'inventory_source_product_id');
        });
    }

    public function inventory(?string $warehouse_code = null): HasMany
    {
        return $this->hasMany(Inventory::class)
            ->whereNull('deleted_at')
            ->whereHas('warehouse', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($warehouse_code, function ($query) use ($warehouse_code) {
                $query->where(['warehouse_code' => $warehouse_code]);
            })
            ->orderBy('warehouse_code')
            ->keyBy('warehouse_code');
    }

    /**
     * @return HasOne|Inventory
     */
    public function userInventory(): HasOne
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->hasOne(Inventory::class)
            ->where(['warehouse_id' => $user->warehouse_id]);
    }

    public function prices(?string $warehouse_code = null): HasMany
    {
        return $this->hasMany(ProductPrice::class)
            ->whereHas('warehouse', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($warehouse_code, function ($query) use ($warehouse_code) {
                $query->where(['warehouse_code' => $warehouse_code]);
            })
            ->keyBy('warehouse_code');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(ProductAlias::class);
    }

    public function saleTax(): HasOne
    {
        return $this->hasOne(SaleTax::class, 'code', 'default_tax_code');
    }

    public static function findBySKU(string $sku): Model|Builder|null
    {
        return static::query()
            ->whereIn('id', ProductAlias::query()->select('product_id')->where(['alias' => $sku]))
            ->first();
    }

    public function isInStock(): bool
    {
        return $this->quantity_available > 0;
    }

    public function inventoryMovementsStatistics(?string $warehouse_code = null): HasMany
    {
        return $this->hasMany(InventoryMovementsStatistic::class)
            ->when($warehouse_code, function ($query) use ($warehouse_code) {
                $query->where(['warehouse_code' => $warehouse_code]);
            });
    }

    public function productDescriptions(): HasMany
    {
        return $this->hasMany(ProductDescription::class);
    }

    public function productPictures(): HasMany
    {
        return $this->hasMany(ProductPicture::class);
    }

    public function productPicture(): HasOne
    {
        return $this->hasOne(ProductPicture::class)->orderBy('priority');
    }

    public function assemblyProducts(): HasMany
    {
        return $this->hasMany(AssemblyProductsElement::class, 'assembly_product_id');
    }

    public function modelTags(): HasMany
    {
        return $this->hasMany(ModelTag::class, 'model_id')
            ->where(['model_type' => self::class]);
    }
}
