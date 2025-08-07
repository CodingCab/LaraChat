<?php

namespace Tests\Feature\Settings\Modules\QuantityDiscounts\Id;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/settings/modules/quantity-discounts/{id}';

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

        $response = $this->get($this->uri);

        $response->assertForbidden();
    }

    #[Test]
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $discount = QuantityDiscount::factory()->create();

        $response = $this->get(str_replace('{id}', $discount->id, $this->uri));

        $response->assertOk();
    }
}
