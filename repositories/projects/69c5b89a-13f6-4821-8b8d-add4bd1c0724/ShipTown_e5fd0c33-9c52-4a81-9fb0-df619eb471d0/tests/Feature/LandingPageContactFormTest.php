<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LandingPageContactFormTest extends TestCase
{
    #[Test]
    public function landing_page_contains_contact_form(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('mailto:support@myshiptown.com');
        $response->assertSee('contact-name');
        $response->assertSee('contact-email');
        $response->assertSee('contact-message');
    }
}
