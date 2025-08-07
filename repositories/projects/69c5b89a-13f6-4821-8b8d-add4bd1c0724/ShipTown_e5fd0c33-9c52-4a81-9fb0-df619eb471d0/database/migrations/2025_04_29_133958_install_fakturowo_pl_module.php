<?php

use Illuminate\Database\Migrations\Migration;
use App\Modules\Fakturowo\src\FakturowoServiceProvider;

return new class extends Migration
{
    public function up(): void
    {
        FakturowoServiceProvider::installModule();
    }
};
