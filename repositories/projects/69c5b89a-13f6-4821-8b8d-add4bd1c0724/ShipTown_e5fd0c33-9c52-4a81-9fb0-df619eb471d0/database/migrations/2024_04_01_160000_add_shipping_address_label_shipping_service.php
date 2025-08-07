<?php

use App\Models\ShippingService;
use App\Modules\AddressLabel\src\Services;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ShippingService::query()
            ->updateOrCreate([
                'code' => 'billing_address_label',
            ], [
                'service_provider_class' => Services\BillingAddressLabelShippingService::class,
            ]);
    }
};
