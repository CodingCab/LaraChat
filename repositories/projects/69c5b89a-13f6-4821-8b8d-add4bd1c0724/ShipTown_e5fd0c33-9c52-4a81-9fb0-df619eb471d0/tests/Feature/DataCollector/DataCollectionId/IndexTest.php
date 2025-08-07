<?php

namespace Tests\Feature\DataCollector\DataCollectionId;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = 'data-collector';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }

    #[Test]
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => Warehouse::factory()->create()->getKey(),
            'name' => 'test',
        ]);

        $response = $this->get($this->uri.'/'.$dataCollection->id);

        $response->assertSuccessful();
    }

    #[Test]
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => Warehouse::factory()->create()->getKey(),
            'name' => 'test',
        ]);

        $response = $this->get($this->uri.'/'.$dataCollection->id);

        $response->assertSuccessful();
    }
}
