<?php

namespace Tests\Feature\Csv\ProductsPickedInWarehouse;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Csv\ProductsPickedInWarehouse
 */
class IndexTest extends TestCase
{
    #[Test]
    public function index_returns_an_ok_response(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user, 'web')->get(route('warehouse_picks.csv'));

        $response->assertOk();
    }
}
