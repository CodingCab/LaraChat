<?php

use App\Models\ManualRequestJob;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ManualRequestJob::query()->firstOrCreate([
            'job_class' => SyncMissingReservationsJob::class,
        ], [
            'job_name' => 'Active Orders Inventory Reservations - Sync Missing Reservations',
        ]);
    }

    public function down(): void
    {
        ManualRequestJob::query()
            ->where('job_class', SyncMissingReservationsJob::class)
            ->delete();
    }
};