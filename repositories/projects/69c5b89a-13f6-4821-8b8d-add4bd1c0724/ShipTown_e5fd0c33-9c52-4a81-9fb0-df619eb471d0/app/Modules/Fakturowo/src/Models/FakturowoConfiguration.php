<?php

namespace App\Modules\Fakturowo\src\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderAddress.
 *
 * @property int $id
 * @property string $connection_code
 * @property string $api_key
 * @property string|null $api_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class FakturowoConfiguration extends Model
{
    protected $table = 'modules_fakturowo_configuration';

    protected $fillable = [
        'connection_code',
        'api_key',
        'api_url',
    ];

    protected $appends = [
        'api_key',
    ];

    protected $guarded = [
        'api_key',
    ];

    public function getApiKeyAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['api_key_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['api_key_encrypted'] = Crypt::encryptString($value);
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(FakturowoConfiguration::class)
            ->allowedSorts([
                'id',
                'connection_code',
                'api_key',
                'api_url',
            ]);
    }
}
