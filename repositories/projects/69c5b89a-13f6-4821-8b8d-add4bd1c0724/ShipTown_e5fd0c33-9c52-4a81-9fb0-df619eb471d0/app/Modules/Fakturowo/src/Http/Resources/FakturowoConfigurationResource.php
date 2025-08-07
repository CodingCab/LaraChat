<?php

namespace App\Modules\Fakturowo\src\Http\Resources;

use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FakturowoConfiguration
 */
class FakturowoConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'connection_code' => $this->connection_code,
            'api_url' => $this->api_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
