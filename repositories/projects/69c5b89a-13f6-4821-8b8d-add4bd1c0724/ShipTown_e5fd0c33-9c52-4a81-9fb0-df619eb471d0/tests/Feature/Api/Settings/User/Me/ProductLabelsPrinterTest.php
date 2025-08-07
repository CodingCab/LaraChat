<?php

namespace Tests\Feature\Api\Settings\User\Me;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductLabelsPrinterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_product_labels_printer_preference_is_saved(): void
    {
        $params = [
            'printers' => [
                'product_labels' => 2,
            ],
        ];

        $response = $this->postJson(route('api.settings.user.me.store', $params));

        $response->assertOk();
        $response->assertJsonPath('data.printers.product_labels', '2');
    }
}
