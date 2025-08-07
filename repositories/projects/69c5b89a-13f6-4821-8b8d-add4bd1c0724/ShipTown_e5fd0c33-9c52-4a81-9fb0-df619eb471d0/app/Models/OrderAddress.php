<?php

namespace App\Models;

use App\BaseModel;
use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\OrderAddress.
 *
 * @property int $id
 * @property string $company
 * @property string $gender
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $address1
 * @property string $address2
 * @property string $postcode
 * @property string $city
 * @property string $state_code
 * @property string $state_name
 * @property string $country_code
 * @property string $country_name
 * @property string $locker_box_code
 * @property string $phone
 * @property string $fax
 * @property string $website
 * @property string $region
 * @property string $document_type
 * @property string $document_number
 * @property string $tax_id
 * @property string $tax_id_first_3_chars_md5
 * @property string $last_name_first_3_chars_md5
 * @property string|null $discount_code
 * @property bool $tax_exempt
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property bool encrypted
 *
 * @method static Builder|OrderAddress newModelQuery()
 * @method static Builder|OrderAddress newQuery()
 * @method static Builder|OrderAddress query()
 * @method static Builder|OrderAddress whereAddress1($value)
 * @method static Builder|OrderAddress whereAddress2($value)
 * @method static Builder|OrderAddress whereCity($value)
 * @method static Builder|OrderAddress whereCompany($value)
 * @method static Builder|OrderAddress whereCountryCode($value)
 * @method static Builder|OrderAddress whereCountryName($value)
 * @method static Builder|OrderAddress whereCreatedAt($value)
 * @method static Builder|OrderAddress whereDeletedAt($value)
 * @method static Builder|OrderAddress whereFax($value)
 * @method static Builder|OrderAddress whereFirstName($value)
 * @method static Builder|OrderAddress whereGender($value)
 * @method static Builder|OrderAddress whereId($value)
 * @method static Builder|OrderAddress whereLastName($value)
 * @method static Builder|OrderAddress wherePhone($value)
 * @method static Builder|OrderAddress wherePostcode($value)
 * @method static Builder|OrderAddress whereRegion($value)
 * @method static Builder|OrderAddress whereStateCode($value)
 * @method static Builder|OrderAddress whereStateName($value)
 * @method static Builder|OrderAddress whereUpdatedAt($value)
 * @method static Builder|OrderAddress whereWebsite($value)
 * @method static Builder|OrderAddress whereDiscountCode($value)
 * @method static Builder|OrderAddress whereTaxExempt($value)
 *
 * @mixin Eloquent
 */
class OrderAddress extends BaseModel
{
    use HasFactory;

    protected $table = 'orders_addresses';

    protected $fillable = [
        'company',
        'gender',
        'first_name',
        'last_name',
        'email',
        'address1',
        'address2',
        'postcode',
        'city',
        'state_code',
        'state_name',
        'country_code',
        'country_name',
        'locker_box_code',
        'phone',
        'fax',
        'website',
        'region',
        'document_type',
        'document_number',
        'tax_id',
        'tax_id_first_3_chars_md5',
        'last_name_first_3_chars_md5',
        'discount_code',
        'tax_exempt',
    ];

    protected $appends = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'document_number',
        'tax_id',
    ];

    protected $hidden = [
        'first_name_encrypted',
        'last_name_encrypted',
        'last_name_first_3_chars_md5',
        'email_encrypted',
        'phone_encrypted',
        'document_number_encrypted'
    ];

    protected function casts(): array
    {
        return [
            'phone_encrypted' => 'encrypted',
        ];
    }

    public function getFirstNameAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['first_name_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name_encrypted'] = Crypt::encryptString($value);
    }

    public function getLastNameAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['last_name_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name_encrypted'] = Crypt::encryptString($value);
        $this->attributes['last_name_first_3_chars_md5'] = md5(strtolower(substr($value, 0, 3)));
    }

    public function getLastNameFirst3CharsMd5Attribute(): string
    {
        return $this->attributes['last_name_first_3_chars_md5'] ?? '';
    }

    public function getDocumentNumberAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['document_number_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setDocumentNumberAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['document_number_encrypted'] = null;
        } else {
            $this->attributes['document_number_encrypted'] = Crypt::encryptString($value);
        }
    }

    public function getTaxIdAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['tax_id_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setTaxIdAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['tax_id_encrypted'] = null;
            $this->attributes['tax_id_first_3_chars_md5'] = null;
        } else {
            $this->attributes['tax_id_encrypted'] = Crypt::encryptString($value);
            $this->attributes['tax_id_first_3_chars_md5'] = md5(strtolower(substr($value, 0, 3)));
        }
    }

    public function getTaxIdFirst3CharsMd5Attribute(): string
    {
        return $this->attributes['tax_id_first_3_chars_md5'] ?? '';
    }

    public function getPhoneAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['phone_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone_encrypted'] = Crypt::encryptString($value);
    }

    public function getEmailAttribute(): string
    {
        try {
            return Crypt::decryptString($this->attributes['email_encrypted']);
        } catch (Exception $exception) {
            return '';
        }
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email_encrypted'] = Crypt::encryptString($value);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    protected function setFullNameAttribute(string $value): void
    {
        $this->first_name = explode(' ', $value)[0];
        $this->last_name = explode(' ', $value)[1];
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_code', 'code');
    }

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(OrderAddress::class)
            ->allowedFilters([AllowedFilter::scope('search', 'whereHasText')]);
    }

    public function scopeWhereHasText(mixed $query, string $text): mixed
    {
        $md5 = md5(strtolower(substr($text, 0, 3)));
        $offset = 0;
        $matches = [];

        $query->where('company', $text)
            ->orWhere('company', 'like', '%'.$text.'%')
            ->orWhere('address1', $text)
            ->orWhere('address1', 'like', '%'.$text.'%')
            ->orWhere('address2', $text)
            ->orWhere('address2', 'like', '%'.$text.'%')
            ->orWhere('postcode', $text)
            ->orWhere('postcode', 'like', '%'.$text.'%')
            ->orWhere('city', $text)
            ->orWhere('city', 'like', '%'.$text.'%')
            ->orWhere('tax_id_first_3_chars_md5', $md5)
            ->orWhere('last_name_first_3_chars_md5', $md5);

        do {
            $items = $query->limit(100)->offset($offset)->get();

            if ($items->isEmpty()) {
                break;
            }

            $items = $items->filter(function (OrderAddress $item) use ($text) {
                if ($item->tax_id_encrypted !== null && str_contains(Crypt::decryptString($item->tax_id_encrypted), $text)) {
                    return true;
                }
                if ($item->last_name_encrypted !== null && str_contains(Crypt::decryptString($item->last_name_encrypted), $text)) {
                    return true;
                }
                return false;
            });

            $matches = array_merge($matches, $items->pluck('id')->toArray());

            $offset += 100;
        } while ($items->isNotEmpty());

        return $query->whereIn('id', $matches);
    }
}
