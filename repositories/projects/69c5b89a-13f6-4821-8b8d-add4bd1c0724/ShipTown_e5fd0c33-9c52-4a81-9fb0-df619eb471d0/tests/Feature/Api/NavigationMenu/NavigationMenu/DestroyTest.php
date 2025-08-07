<?php

namespace Tests\Feature\Api\NavigationMenu\NavigationMenu;
use PHPUnit\Framework\Attributes\Test;

use App\Models\NavigationMenu;
use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private function simulationTest()
    {
        $navigationMenu = NavigationMenu::create([
            'name' => 'testing',
            'url' => 'testing',
            'group' => 'picklist',
        ]);

        return $this->delete(route('api.navigation-menu.destroy', $navigationMenu));
    }

    #[Test]
    public function test_delete_call_returns_ok(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->simulationTest();

        $response->assertSuccessful();
    }

    public function test_delete_call_should_be_loggedin(): void
    {
        $response = $this->simulationTest();

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function test_delete_removes_record_from_database(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $navigationMenu = NavigationMenu::create([
            'name' => 'testing',
            'url' => 'testing',
            'group' => 'picklist',
        ]);

        $this->delete(route('api.navigation-menu.destroy', $navigationMenu))
            ->assertSuccessful();

        $this->assertDatabaseMissing('navigation_menu', [
            'id' => $navigationMenu->id,
        ]);
    }
}
