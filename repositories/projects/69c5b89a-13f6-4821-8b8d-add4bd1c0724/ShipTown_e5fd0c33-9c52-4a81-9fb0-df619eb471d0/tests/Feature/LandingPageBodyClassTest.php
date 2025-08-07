<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LandingPageBodyClassTest extends TestCase
{
    #[Test]
    public function landing_page_has_custom_body_class(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('<body class="landing-page"', false);
    }
}
