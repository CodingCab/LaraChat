<?php

namespace App\Modules\AssemblyProducts\src\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $assembly_product_id
 * @property int $simple_product_id
 * @property int $required_quantity
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Product $assemblyProduct
 * @property Product $simpleProduct
 */
class AssemblyProductsElement extends Model
{
    use HasFactory;
    protected $fillable = [
        'assembly_product_id',
        'simple_product_id',
        'required_quantity',
    ];

    public function assemblyProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'assembly_product_id');
    }

    public function simpleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'simple_product_id');
    }
}
