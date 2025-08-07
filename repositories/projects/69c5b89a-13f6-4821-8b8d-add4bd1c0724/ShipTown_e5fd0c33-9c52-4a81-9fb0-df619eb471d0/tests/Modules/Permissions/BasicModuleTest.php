<?php

namespace Tests\Modules\Permissions;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Permissions\src\Jobs\CreateRoutePermissionsJob;
use Tests\TestCase;
use App\Modules\Permissions\src\PermissionsModuleServiceProvider;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PermissionsModuleServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        CreateRoutePermissionsJob::dispatch();

        $this->assertDatabaseHas('permissions', [
            'name' => 'api.admin.users.index',
        ]);
    }
}
