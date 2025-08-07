<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class DataCollectionOfflineInventory extends DataCollection
{
    protected static function booted(): void
    {
        static::addGlobalScope('OfflineInventory', function (Builder $builder) {
            $builder->where('type', '=', self::class);
        });
    }

    public function save(array $options = []): bool
    {
        $this->type = self::class;

        return parent::save($options);
    }
}
