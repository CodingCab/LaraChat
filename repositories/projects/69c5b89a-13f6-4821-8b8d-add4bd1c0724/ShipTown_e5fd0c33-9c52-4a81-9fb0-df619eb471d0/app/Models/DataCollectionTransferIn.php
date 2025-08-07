<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 *  TransferIn
 */
class DataCollectionTransferIn extends DataCollection
{
    protected static function booted()
    {
        static::addGlobalScope('TransferIn', function (Builder $builder) {
            $builder->where('type', '=', self::class);
        });
    }

    public function save(array $options = []): bool
    {
        $this->type = self::class;

        return parent::save($options);
    }
}
