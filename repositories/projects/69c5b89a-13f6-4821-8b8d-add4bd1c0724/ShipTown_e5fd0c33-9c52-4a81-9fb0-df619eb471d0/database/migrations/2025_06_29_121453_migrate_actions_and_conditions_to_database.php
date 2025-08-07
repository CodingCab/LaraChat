<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Copy conditions from config file
        $conditions = [
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition::class,
                'description' => 'Order Status Code is',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeInCondition::class,
                'description' => 'Order Status Code is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\StatusCodeNotInCondition::class,
                'description' => 'Order Status Code is not in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderNumberEqualsCondition::class,
                'description' => 'Order Number equals',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderNumberInCondition::class,
                'description' => 'Order Number is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderNumberContainsCondition::class,
                'description' => 'Order Number contains',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderNumberStartsWithCondition::class,
                'description' => 'Order Number starts with',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HasTagsCondition::class,
                'description' => 'Order has tags',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\DoesntHaveTagsCondition::class,
                'description' => 'Order does not have tags',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HasAnyShipmentCondition::class,
                'description' => 'Order Has Shipment',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\IsActiveCondition::class,
                'description' => 'Order Is Active',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\IsPartiallyPaidCondition::class,
                'description' => 'Order Is Partially Paid',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPaidCondition::class,
                'description' => 'Order Is Fully Paid',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPickedCondition::class,
                'description' => 'Order Is Fully Picked',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition::class,
                'description' => 'Order Is Fully Packed',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition::class,
                'description' => 'Shipping Method Code equals',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeInCondition::class,
                'description' => 'Shipping Method Code is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodNameEqualsCondition::class,
                'description' => 'Shipping Method Name equals',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodNameInCondition::class,
                'description' => 'Shipping Method Name is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingAddressCountryCodeInCondition::class,
                'description' => 'Shipping Address Country Code is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\ShippingAddressCountryCodeNotInCondition::class,
                'description' => 'Shipping Address Country Code is not in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\LabelTemplateInCondition::class,
                'description' => 'Shipping Label Template is in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\CourierLabelTemplateIsNotInCondition::class,
                'description' => 'Shipping Label Template is not in',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HoursSincePlacedAtCondition::class,
                'description' => 'Hours Since Placed more than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtCondition::class,
                'description' => 'Hours Since Last Updated more than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\LineCountEqualsCondition::class,
                'description' => 'Total Line Count equals',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\LineCountLessThanCondition::class,
                'description' => 'Total Line Count less than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\LineCountGreaterThanCondition::class,
                'description' => 'Total Line Count greater than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\TotalQuantityToShipEqualsCondition::class,
                'description' => 'Total Quantity To Ship equals',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderTotalWeightGreaterThanCondition::class,
                'description' => 'Total Order Weight is greater than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderTotalWeightLowerThanCondition::class,
                'description' => 'Total Order Weight is lower than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderTotalVolumetricWeightGreaterThanCondition::class,
                'description' => 'Total Order Volumetric Weight is greater than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\OrderTotalVolumetricWeightLowerThanCondition::class,
                'description' => 'Total Order Volumetric Weight is lower than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\AnyProductHasTagCondition::class,
                'description' => 'Products have tags',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition::class,
                'description' => 'Products Can Fulfill from Warehouse Code (0 for all)',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\CanNotFulfillFromLocationCondition::class,
                'description' => 'Products Can NOT Fulfill Warehouse Code (0 for all)',
            ],
        ];

        foreach ($conditions as $condition) {
            DB::table('modules_automations_available_conditions')->updateOrInsert(
                ['class' => $condition['class']],
                ['description' => $condition['description']]
            );
        }

        // Copy actions from config file
        $actions = [
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SetStatusCodeAction::class,
                'description' => 'Set Status Code',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction::class,
                'description' => 'Set Shipping Label Template',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SendOrderEmailAction::class,
                'description' => 'Send Email Template to customer',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SendOrderEmailToAddressAction::class,
                'description' => 'Send Email Template to address',
            ],
            [
                'class' => \App\Modules\Slack\src\Automations\SendSlackNotificationAction::class,
                'description' => 'Send Slack notification',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\ShipRemainingProductsAction::class,
                'description' => 'Ship all products from warehouse code',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\AddOrderCommentAction::class,
                'description' => 'Add order comment',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\LogMessageAction::class,
                'description' => 'Add log message',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\AttachTagsAction::class,
                'description' => 'Add Order Tag',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\DetachTagsAction::class,
                'description' => 'Remove Detach tags',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SplitOrderToWarehouseCodeAction::class,
                'description' => 'Split Order to warehouse code',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\SplitBundleSkuAction::class,
                'description' => 'Split bundle SKU (format: BundleSKU,SKU1,SKU2...)',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\PushToBoxTopOrderAction::class,
                'description' => 'Create Warehouse Shipment in BoxTop Software',
            ],
            [
                'class' => \App\Modules\Automations\src\Actions\Order\PrintAddressLabelAction::class,
                'description' => 'Print Address Label on printer ID, template name',
            ],
            [
                'class' => \App\Modules\Fakturowo\src\OrderActions\RaiseFakturowoPLInvoice::class,
                'description' => 'Raise FakturowoPL Invoice',
            ],
        ];

        foreach ($actions as $action) {
            DB::table('modules_automations_available_actions')->updateOrInsert(
                ['class' => $action['class']],
                ['description' => $action['description']]
            );
        }
    }
};
