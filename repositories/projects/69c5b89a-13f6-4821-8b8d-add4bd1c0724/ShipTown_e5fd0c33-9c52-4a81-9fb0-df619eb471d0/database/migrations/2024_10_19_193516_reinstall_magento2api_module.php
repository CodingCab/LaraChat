<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\Module::query()
            ->where('service_provider_class', \App\Modules\Magento2MSI\src\Magento2MsiServiceProvider::class)
            ->update(['service_provider_class' => \App\Modules\Magento2API\InventorySync\src\InventorySyncServiceProvider::class]);

        \App\Models\Module::query()
            ->where('service_provider_class', \App\Modules\MagentoApi\src\EventServiceProviderBase::class)
            ->update(['service_provider_class' => \App\Modules\Magento2API\PriceSync\src\PriceSyncServiceProvider::class]);
    }
};
