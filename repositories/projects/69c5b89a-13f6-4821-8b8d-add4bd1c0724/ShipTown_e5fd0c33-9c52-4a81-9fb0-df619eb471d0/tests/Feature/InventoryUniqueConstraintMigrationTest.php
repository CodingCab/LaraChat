<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InventoryUniqueConstraintMigrationTest extends TestCase
{
    #[Test]
    public function migration_runs_when_index_already_exists(): void
    {
        $result = DB::selectOne(
            "SHOW INDEX FROM inventory WHERE Key_name = 'inventory_product_id_warehouse_id_unique'"
        );
        $this->assertNotNull($result, 'Expected unique index to exist before migration');

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_06_17_115927_add_unique_constraint_to_inventory_table.php',
            '--force' => true,
        ]);

        $this->assertNotNull(
            DB::selectOne(
                "SHOW INDEX FROM inventory WHERE Key_name = 'inventory_product_id_warehouse_id_unique'"
            )
        );
    }
}
