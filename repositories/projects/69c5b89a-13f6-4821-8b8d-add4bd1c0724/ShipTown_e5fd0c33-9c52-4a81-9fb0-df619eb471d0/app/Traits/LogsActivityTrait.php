<?php

namespace App\Traits;

use App\BaseModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait LogsActivityTrait.
 *
 * @mixin BaseModel
 */
trait LogsActivityTrait
{
    use LogsActivity;

    public function log($message, array $properties = []): self
    {
        activity()->on($this)
            ->withProperties($properties)
            ->log($message);

        return $this;
    }

    public function logActivity($message, array $properties = []): self
    {
        activity()->on($this)
            ->withProperties($properties)
            ->log($message);

        return $this;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
