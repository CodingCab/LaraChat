<?php

namespace Tests\Feature\Api\Pdf\Preview;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function store_returns_an_ok_response(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('api/pdf/preview', [
            'data' => [
                'labels' => ['label1', 'label2'],
            ],
            'template' => 'shelf-labels/6x4in-1-per-page',
        ]);

        $response->assertOk();

    }
}
