<?php

namespace App\Http\Resources;

use App\Models\Session;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Session
 */
class SessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'last_activity' => $this->last_activity,

            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
