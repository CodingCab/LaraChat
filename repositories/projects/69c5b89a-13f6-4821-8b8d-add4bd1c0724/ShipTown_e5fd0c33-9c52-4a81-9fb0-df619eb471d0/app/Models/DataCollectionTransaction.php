<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 *  TransferIn
 */
class DataCollectionTransaction extends DataCollection
{
    protected static function booted(): void
    {
        static::addGlobalScope('Transaction', function (Builder $builder) {
            $builder->where('type', '=', self::class);
        });
    }

    public function save(array $options = []): bool
    {
        $this->type = self::class;

        return parent::save($options);
    }
}
