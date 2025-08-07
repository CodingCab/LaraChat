<?php

use App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\OrderTotalWeightGreaterThanCondition;
use App\Modules\Automations\src\Models\Automation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $automation = Automation::create([
            'name' => 'Heavy Order Pallet Shipment',
            'description' => 'Automatically sets the courier label to "raben_pallet" and the status to "pallets_shipment" for orders with a total weight exceeding {n} kg.',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => OrderTotalWeightGreaterThanCondition::class,
            'condition_value' => 30,
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'pallets_shipment',
        ]);

        $automation->actions()->create([
            'action_class' => SetLabelTemplateAction::class,
            'action_value' => 'raben_pallet',
        ]);

        $automation->update(['enabled' => true]);
    }
};
