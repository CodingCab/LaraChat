<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LandingPagePricingTest extends TestCase
{
    #[Test]
    public function landing_page_displays_pricing_section(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('Pricing');
        $response->assertSee('Free');
        $response->assertSee('Business');
        $response->assertSee('Enterprise');
    }
}

