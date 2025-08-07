<?php

namespace App\Modules\SalesTaxes\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\PaymentType.
 *
 * @property int $id
 * @property string $code
 * @property string $rate
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SaleTax extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'rate',
    ];


    protected $table = 'modules_sale_taxes';
}
