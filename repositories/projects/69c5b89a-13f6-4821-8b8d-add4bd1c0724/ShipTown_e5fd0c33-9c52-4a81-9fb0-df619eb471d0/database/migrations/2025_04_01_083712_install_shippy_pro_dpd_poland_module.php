<?php

use App\Modules\Couriers\ShippyPro\Generic\src\ShippyProGenericServiceProvider;
use Illuminate\Database\Migrations\Migration;
use App\Modules\Couriers\ShippyPro\DpdPoland\src\ShippyProDpdPolandServiceProvider;

return new class extends Migration
{
    public function up(): void
    {
        ShippyProDpdPolandServiceProvider::installModule();
        ShippyProGenericServiceProvider::installModule();
    }
};
