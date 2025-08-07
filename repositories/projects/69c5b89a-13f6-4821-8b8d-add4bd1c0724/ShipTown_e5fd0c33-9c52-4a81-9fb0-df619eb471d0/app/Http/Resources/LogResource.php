<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Models\Activity;

/**
 * @mixin Activity
 */
class LogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'created_at' => data_get($this, 'created_at'),
            'id' => data_get($this, 'id'),
            'description' => data_get($this, 'description'),
            'subject_id' => data_get($this, 'subject_id'),
            'subject_type' => data_get($this, 'subject_type'),
            'causer_id' => data_get($this, 'causer_id'),
            'causer_type' => data_get($this, 'causer_type'),
            'properties' => data_get($this, 'properties'),
            'changes' => $this->getChanges(),
            'causer' => $this->whenLoaded('causer'),
        ];
    }

    private function getChanges(): array
    {
        $result = [];

        if (Arr::has($this->properties, ['old'])) {
            if (! Arr::has($this->properties, ['attributes'])) {
                $result = $this->properties['old'];
            } else {
                $array_keys = array_keys($this->properties['attributes']);

                foreach ($array_keys as $key) {
                    if ($this->properties['attributes'][$key] !== $this->properties['old'][$key]) {
                        $result[$key] = $this->properties['attributes'][$key];
                    }
                }
            }
        }

        return $result;
    }
}
