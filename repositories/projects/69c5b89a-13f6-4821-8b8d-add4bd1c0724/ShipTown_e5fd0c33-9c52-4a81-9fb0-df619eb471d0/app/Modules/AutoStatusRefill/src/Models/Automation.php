<?php

namespace App\Modules\AutoStatusRefill\src\Models;

use App\Models\Order;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Automation.
 *
 * @property int $id
 * @property string $from_status_code
 * @property string $to_status_code
 * @property int $desired_order_count
 * @property bool $refill_only_at_0
 *
 * @property-read int     $current_count_with_status
 * @property-read int $required_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Eloquent
 */
class Automation extends Model
{
    use HasFactory;

    protected $table = 'modules_autostatus_picking_configurations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'from_status_code',
        'to_status_code',
        'desired_order_count',
        'refill_only_at_0'
    ];

    public function getRequiredCountAttribute(): int
    {
        return $this->desired_order_count - $this->current_count_with_status;
    }

    public function getCurrentCountWithStatusAttribute(): int
    {
        return Order::whereIn('status_code', [$this->to_status_code])->count();
    }
}
