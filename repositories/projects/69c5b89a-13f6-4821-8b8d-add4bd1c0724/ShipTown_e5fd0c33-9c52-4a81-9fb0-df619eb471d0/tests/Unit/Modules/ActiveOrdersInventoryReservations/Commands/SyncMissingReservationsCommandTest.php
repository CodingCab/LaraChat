<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations\Commands;

use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncMissingReservationsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    public function test_command_dispatches_sync_job()
    {
        Bus::fake();

        $this->artisan('reservations:sync-missing')
            ->expectsOutput('Starting sync of missing inventory reservations...')
            ->expectsOutput('Sync job dispatched successfully.')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncMissingReservationsJob::class);
    }

    public function test_command_accepts_created_after_option()
    {
        Bus::fake();

        $dateTime = '2024-01-01 10:00:00';

        $this->artisan('reservations:sync-missing', ['--created-after' => $dateTime])
            ->expectsOutput('Starting sync of missing inventory reservations...')
            ->expectsOutput('Filtering orders created after: 2024-01-01 10:00:00')
            ->expectsOutput('Sync job dispatched successfully.')
            ->assertExitCode(0);

        Bus::assertDispatched(function (SyncMissingReservationsJob $job) use ($dateTime) {
            return $job->createdAfter && $job->createdAfter->eq(\Carbon\Carbon::parse($dateTime));
        });
    }
}