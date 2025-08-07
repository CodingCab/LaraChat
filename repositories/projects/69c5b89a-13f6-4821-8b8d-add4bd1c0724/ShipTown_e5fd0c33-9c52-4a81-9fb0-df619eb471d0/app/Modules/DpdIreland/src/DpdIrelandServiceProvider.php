<?php

namespace App\Modules\DpdIreland\src;

use App\Models\ShippingService;
use App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction;
use App\Modules\Automations\src\Conditions\Order\LabelTemplateInCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class DpdIrelandServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - DPD Ireland Integration';

    public static string $module_description = 'Provides seamless integration with DPD Ireland';

    public static string $settings_link = '/settings/dpd-ireland';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    public static function installing(): bool
    {
        self::createOrderAutomation();

        return true;
    }

    public static function enabling(): bool
    {
        ShippingService::query()
            ->updateOrCreate([
                'code' => 'dpd_irl_next_day',
            ], [
                'service_provider_class' => Services\NextDayShippingService::class,
            ]);

        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()
            ->where(['code' => 'dpd_irl_next_day'])
            ->delete();

        return true;
    }

    public static function createOrderAutomation(): void
    {
        if (Automation::query()->where('name', 'DPD Ireland Next Day Shipping')->exists()) {
            return;
        }

        $automation = Automation::query()->firstOrCreate([
            'enabled' => false,
            'name' => 'DPD Ireland Next Day Shipping',
            'description' => 'Automatically ship orders with DPD Ireland Next Day Shipping',
        ]);

        Condition::create([
            'automation_id' => $automation->id,
            'condition_class' => LabelTemplateInCondition::class,
            'condition_value' => 'address_label',
        ]);

        Action::create([
            'automation_id' => $automation->id,
            'action_class' => SetLabelTemplateAction::class,
            'action_value' => 'dpd_irl_next_day',
        ]);
    }
}
