<?php

namespace Tests\Feature\Api\ProductsAliases\ProductsAlias;
use PHPUnit\Framework\Attributes\Test;

use App\Models\ProductAlias;
use App\User;
use Faker\Factory as Faker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    #[Test]
    public function testIfCallReturnsOk()
    {
        $faker = Faker::create();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $productAlias = ProductAlias::factory(['alias' => $faker->unique()->word()])->create();

        $response = $this->actingAs($user, 'api')->delete(route('api.products-aliases.destroy', $productAlias));

        $response->assertSuccessful();

        $this->assertModelMissing($productAlias);
    }
}
