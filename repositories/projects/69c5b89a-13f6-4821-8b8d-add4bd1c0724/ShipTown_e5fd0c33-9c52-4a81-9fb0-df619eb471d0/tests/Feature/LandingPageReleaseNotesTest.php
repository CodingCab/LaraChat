<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LandingPageReleaseNotesTest extends TestCase
{
    #[Test]
    public function landingPageHasReleaseNotesLink(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('release-notes');
        $response->assertSee('Release Notes');
    }
}
