<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddApiUrlColumnMigrationTest extends TestCase
{
    #[Test]
    public function migration_runs_when_column_already_exists(): void
    {
        $result = DB::selectOne(
            "SHOW COLUMNS FROM modules_fakturowo_configuration LIKE 'api_url'"
        );
        $this->assertNotNull($result, 'Expected api_url column to exist before migration');

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_07_02_000000_add_api_url_column_if_missing_to_modules_fakturowo_configuration_table.php',
            '--force' => true,
        ]);

        $this->assertNotNull(
            DB::selectOne(
                "SHOW COLUMNS FROM modules_fakturowo_configuration LIKE 'api_url'"
            )
        );
    }
}
