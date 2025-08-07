<?php

use App\Modules\DataCollectorTransactions\src\DataCollectorTransactionsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DataCollectorTransactionsServiceProvider::installModule();
    }
};
