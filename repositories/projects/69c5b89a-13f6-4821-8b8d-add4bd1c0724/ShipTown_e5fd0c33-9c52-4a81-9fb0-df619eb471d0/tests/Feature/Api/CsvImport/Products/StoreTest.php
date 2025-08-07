<?php

namespace Tests\Feature\Api\CsvImport\Products;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Modules\Permissions\src\Models\Role;
use App\Modules\Permissions\src\Models\Permission;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvUploadedFileJob;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    #[Test]
    public function testStoreCallReturnsOk()
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $permission = Permission::firstOrCreate(['name' => 'api.csv-import.products.store']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $fakeFile = UploadedFile::fake()->create('products.csv', 1024, 'text/csv');
        $mappedFields = [
            'sku' => 1,
            'name' => 2,
        ];

        $response = $this->actingAs($user, 'api')->post(route('api.csv-import.products.store'), [
            'file' => $fakeFile,
            'mappedFields' => json_encode($mappedFields),
        ]);

        $response->assertSuccessful();

        $response->assertJson([
            'data' => [
                'success' => true,
                'message' => t('CSV import has been queued for processing.'),
            ],
        ]);

        $this->assertDatabaseHas('modules_csv_uploaded_files', [
            'filename' => 'products.csv',
        ]);

        $uploadedFileEntry = CsvUploadedFile::where('filename', 'products.csv')
            ->latest('id')
            ->first();
        $this->assertNotNull($uploadedFileEntry, 'CsvUploadedFile entry not found.');

        $this->assertEquals($mappedFields, $uploadedFileEntry->mapped_fields);

        Queue::assertPushed(ProcessCsvUploadedFileJob::class);
    }
}
