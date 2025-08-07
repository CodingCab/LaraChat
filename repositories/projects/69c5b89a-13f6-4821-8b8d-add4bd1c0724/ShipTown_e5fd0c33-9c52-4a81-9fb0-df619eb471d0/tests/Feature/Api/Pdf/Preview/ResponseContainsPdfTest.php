<?php

namespace Tests\Feature\Api\Pdf\Preview;

use PHPUnit\Framework\Attributes\Test;
use App\User;
use Tests\TestCase;

class ResponseContainsPdfTest extends TestCase
{
    #[Test]
    public function preview_contains_pdf_output(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('api/pdf/preview', [
            'data' => [
                'labels' => ['test-label'],
            ],
            'template' => 'shelf-labels/6x4in-1-per-page',
        ]);

        $response->assertOk();
        $this->assertGreaterThan(100, strlen($response->getContent()));
    }
}
