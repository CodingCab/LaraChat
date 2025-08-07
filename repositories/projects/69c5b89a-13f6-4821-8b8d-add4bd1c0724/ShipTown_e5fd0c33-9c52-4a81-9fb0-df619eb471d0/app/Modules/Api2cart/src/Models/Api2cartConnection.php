<?php

namespace App\Modules\Api2cart\src\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Modules\Api2cart\src\Models\Api2cartConnection.
 *
 * @property int $id
 * @property string $location_id
 * @property string $type
 * @property string $url
 * @property string $prefix
 * @property string|null $bridge_api_key
 * @property int|null $magento_store_id
 * @property string|null $magento_warehouse_id
 * @property string $last_synced_modified_at
 * @property Carbon|null $min_created_from
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $inventory_location_id
 * @property int|null $pricing_location_id
 * @property-read Collection|Api2cartProductLink[] $productLinks
 * @property-read int|null $product_links_count
 * @property string|null inventory_warehouse_ids
 * @property int    pricing_source_warehouse_id
 * @property string|null inventory_source_warehouse_tag
 * @property int $inventory_source_warehouse_tag_id
 *
 * @mixin Eloquent
 */
class Api2cartConnection extends Model
{
    use HasFactory;

    protected $table = 'modules_api2cart_connections';

    protected $fillable = [
        'prefix',
        'bridge_api_key',
        'inventory_source_warehouse_tag',
        'pricing_source_warehouse_id',
        'last_synced_modified_at',
        'url',
        'type',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes([
            'min_created_from' => Carbon::now()->subMonths(6),
            'last_synced_modified_at' => Carbon::now()->subMonths(6),
        ], true);

        parent::__construct($attributes);
    }

    public function productLinks(): HasMany
    {
        return $this->hasMany(Api2cartProductLink::class);
    }
}
