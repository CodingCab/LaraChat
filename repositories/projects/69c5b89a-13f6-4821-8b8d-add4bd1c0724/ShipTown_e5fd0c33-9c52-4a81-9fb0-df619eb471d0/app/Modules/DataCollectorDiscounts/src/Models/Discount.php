<?php

namespace App\Modules\DataCollectorDiscounts\src\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property int $id
 * @property string $code
 * @property int $percentage_discount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Discount extends Model
{
    use HasFactory;
    use LogsActivityTrait;
    use SoftDeletes;

    protected $table = 'modules_data_collector_discounts';

    protected $fillable = [
        'code',
        'percentage_discount',
    ];

    protected $casts = [
        'percentage_discount' => 'integer',
    ];

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Discount::class)
            ->allowedSorts([
                'id',
                'code',
                'percentage_discount',
            ]);
    }
}
