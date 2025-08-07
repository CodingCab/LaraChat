<?php

namespace Database\Seeders;

use App\Models\ShippingService;
use App\Modules\AddressLabel\src\AddressLabelServiceProvider;
use App\Modules\AddressLabel\src\Services\AddressLabelShippingService;
use Illuminate\Database\Seeder;

class RabenGroupSeeder extends Seeder
{
    public function run(): void
    {
        AddressLabelServiceProvider::installModule();

        ShippingService::query()
            ->updateOrCreate([
                'code' => 'raben_3day',
            ], [
                'service_provider_class' => AddressLabelShippingService::class,
            ]);
    }
}
