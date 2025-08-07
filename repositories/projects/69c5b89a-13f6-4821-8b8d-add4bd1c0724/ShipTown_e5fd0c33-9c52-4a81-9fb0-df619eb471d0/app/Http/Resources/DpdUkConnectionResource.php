<?php

namespace App\Http\Resources;

use App\Modules\DpdUk\src\Models\Connection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Connection
 */
class DpdUkConnectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'username' => $this->username,
            'password' => '***',
            'collection_address_id' => $this->collection_address_id,
            'geo_session' => $this->geo_session,
            'collection_address' => OrderAddressResource::make($this->collectionAddress()->first()),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
