<?php

namespace App\Modules\Slack\src\Http\Resources;

use App\Modules\Slack\src\Models\SlackConfig;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @mixin SlackConfig
 */
class SlackConfigResource extends JsonResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'incoming_webhook_url' => $this->incoming_webhook_url,
        ];
    }
}
