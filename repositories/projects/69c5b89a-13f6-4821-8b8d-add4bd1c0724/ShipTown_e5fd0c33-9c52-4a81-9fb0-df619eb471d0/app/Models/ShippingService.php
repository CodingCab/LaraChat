<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int id
 * @property string code
 * @property string service_provider_class
 * @property array connection_details
 * @property string|null connection_details_encrypted
 */
class ShippingService extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'code',
        'service_provider_class',
        'connection_details'
    ];

    protected $appends = [
        'connection_details',
    ];

    protected $hidden = [
        'connection_details_encrypted',
    ];

    public function getConnectionDetailsAttribute()
    {
        if (isset($this->attributes['connection_details_encrypted'])) {
            return json_decode(Crypt::decryptString($this->attributes['connection_details_encrypted']), true);
        }

        return null;
    }

    public function setConnectionDetailsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['connection_details_encrypted'] = Crypt::encryptString(json_encode($value));
        } else {
            $this->attributes['connection_details_encrypted'] = null;
        }
    }

    /**
     * @return mixed
     */
    public static function getSpatieQueryBuilder()
    {
        return QueryBuilder::for(ShippingService::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('code'),
            ])
            ->allowedSorts([
                'id',
                'code',
            ]);
    }
}
