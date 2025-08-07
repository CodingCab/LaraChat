<?php

namespace Tests\Console\Commands;

use App\Models\Module;
use Tests\TestCase;

class AppInstallModulesCommandTest extends TestCase
{
    public function test_it_installs_modules_and_removes_missing_ones(): void
    {
        $this->artisan('db:wipe');
        $this->artisan('migrate');

        Module::query()->insert([
            'service_provider_class' => 'NonExisting\\Module\\Provider',
            'enabled' => false,
            'name' => 'Fake',
            'description' => '',
            'settings_link' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('app:install-modules')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('modules', [
            'service_provider_class' => 'NonExisting\\Module\\Provider',
        ]);

        $this->assertDatabaseHas('modules', [
            'service_provider_class' => 'App\\Modules\\InventoryTotals\\src\\InventoryTotalsServiceProvider',
        ]);
    }
}
