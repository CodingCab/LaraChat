<?php

use Illuminate\Database\Migrations\Migration;
use App\Modules\Couriers\ShippyPro\InPostPoland\src\ShippyProInPostPolandServiceProvider;

return new class extends Migration
{
    public function up(): void
    {
        ShippyProInPostPolandServiceProvider::installModule();
    }
};
