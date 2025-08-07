<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Commands;

use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use Illuminate\Console\Command;

class SyncMissingReservationsCommand extends Command
{
    protected $signature = 'reservations:sync-missing {--created-after= : Only sync orders created after this datetime}';

    protected $description = 'Sync missing inventory reservations for order products that were created without triggering events';

    public function handle(): int
    {
        $this->info('Starting sync of missing inventory reservations...');

        $createdAfter = $this->option('created-after')
            ? \Carbon\Carbon::parse($this->option('created-after'))
            : null;

        if ($createdAfter) {
            $this->info('Filtering orders created after: ' . $createdAfter->toDateTimeString());
        }

        SyncMissingReservationsJob::dispatch($createdAfter);

        $this->info('Sync job dispatched successfully.');

        return 0;
    }
}
