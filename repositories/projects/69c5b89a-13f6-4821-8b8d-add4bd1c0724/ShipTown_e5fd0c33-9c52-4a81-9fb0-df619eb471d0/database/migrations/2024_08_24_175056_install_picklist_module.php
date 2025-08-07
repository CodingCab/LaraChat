<?php

use App\Modules\Picklist\src\PicklistServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PicklistServiceProvider::enableModule();
    }
};
