<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataCollectionStocktake extends DataCollection
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('stocktakeType', function (Builder $builder) {
            $builder->where('type', '=', self::class);
        });
    }

    public function save(array $options = []): bool
    {
        $this->type = self::class;

        return parent::save($options);
    }
}
