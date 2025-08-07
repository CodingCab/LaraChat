<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManifestJsonCacheTest extends TestCase
{
    #[Test]
    public function cache_control_header_is_public_for_24_hours(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('manifest.json'));

        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
    }
}
