<?php

use App\Modules\Magento2API\InventorySync\src\InventorySyncServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        InventorySyncServiceProvider::installModule();
    }
};
