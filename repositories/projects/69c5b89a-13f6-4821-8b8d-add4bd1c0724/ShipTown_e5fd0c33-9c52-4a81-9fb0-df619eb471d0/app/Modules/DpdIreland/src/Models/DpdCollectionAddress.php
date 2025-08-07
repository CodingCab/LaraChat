<?php

namespace App\Modules\DpdIreland\src\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpdCollectionAddress.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DpdCollectionAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DpdCollectionAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DpdCollectionAddress query()
 *
 * @mixin \Eloquent
 */
class DpdCollectionAddress extends Model
{
    /**
     * @return string[]
     */
    public static function getCollectionAddress(): array
    {
        return [
            'Contact' => config('dpd.collection_address.contact'),
            'ContactTelephone' => config('dpd.collection_address.contact_telephone'),
            'ContactEmail' => config('dpd.collection_address.contact_email'),
            'BusinessName' => config('dpd.collection_address.business_name'),
            'AddressLine1' => config('dpd.collection_address.address_line1'),
            'AddressLine2' => config('dpd.collection_address.address_line2'),
            'AddressLine3' => config('dpd.collection_address.address_line3'),
            'AddressLine4' => config('dpd.collection_address.address_line4'),
            'CountryCode' => config('dpd.collection_address.country_code'),
        ];
    }
}
