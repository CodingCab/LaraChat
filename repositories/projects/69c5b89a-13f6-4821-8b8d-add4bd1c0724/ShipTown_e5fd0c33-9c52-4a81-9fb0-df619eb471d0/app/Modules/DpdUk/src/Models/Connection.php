<?php

namespace App\Modules\DpdUk\src\Models;

use App\BaseModel;
use App\Models\OrderAddress;
use App\Traits\Encryptable;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\Configuration.
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $account_number
 * @property int $collection_address_id
 * @property OrderAddress $collection_address
 * @property string $geo_session
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property OrderAddress collectionAddress
 *
 * @mixin Eloquent
 */
class Connection extends BaseModel
{
    use Encryptable;
    use HasFactory;

    protected $table = 'modules_dpduk_connections';

    protected $fillable = [
        'username',
        'password',
        'account_number',
        'collection_address_id',
        'geo_session',
    ];

    protected $hidden = ['password'];

    protected $guarded = [
        'password',
    ];

    protected array $encryptable = [
        'username',
        'password',
        'account_number',
        'geo_session',
    ];

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(self::class)
            ->allowedFilters([])
            ->allowedIncludes([])
            ->allowedSorts([]);
    }

    public function collectionAddress(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class);
    }
}
