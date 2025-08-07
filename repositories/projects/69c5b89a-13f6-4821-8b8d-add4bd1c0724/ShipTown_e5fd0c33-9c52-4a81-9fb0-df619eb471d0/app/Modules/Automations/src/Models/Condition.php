<?php

namespace App\Modules\Automations\src\Models;

use App\BaseModel;
use App\Models\Order;
use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int automation_id
 * @property string condition_class
 * @property string condition_value
 * @property Automation automation
 */
class Condition extends BaseModel
{
    use HasFactory;

    protected $table = 'modules_automations_conditions';

    protected $fillable = [
        'automation_id',
        'condition_class',
        'condition_value',
    ];

    public function isTrue(Order $order): bool
    {
        $validator = new $this->condition_class($order);

        return $validator->isValid($this->condition_value);
    }

    public function condition()
    {
        /** @var BaseOrderConditionAbstract $condition */
        $condition = $this->condition_class;

        return $condition;
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}
