<?php

namespace App;

use App\Traits\LogsActivityTrait;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel.
 *
 * @mixin Eloquent
 */
abstract class BaseModel extends Model
{
    use HasFactory;
    use LogsActivityTrait;

    public static function getTableName(): string
    {
        return (new static())->getTable();
    }

    public function isAttributeChanged(string $attribute): bool
    {
        return $this->getOriginal($attribute) !== $this->getAttribute($attribute);
    }

    public function isAttributeNotChanged(string $attribute): bool
    {
        return ! $this->isAttributeChanged($attribute);
    }

    public function isAnyAttributeChanged(array $attributes): bool
    {
        $changedAttributes = collect($attributes)->filter(function (string $attribute) {
            return $this->isAttributeChanged($attribute);
        });

        return $changedAttributes->isNotEmpty();
    }
}
