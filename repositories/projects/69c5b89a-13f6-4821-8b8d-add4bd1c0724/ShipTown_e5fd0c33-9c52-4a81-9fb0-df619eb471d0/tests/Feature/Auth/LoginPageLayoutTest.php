<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginPageLayoutTest extends TestCase
{
    #[Test]
    public function login_page_contains_layout_classes(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('login-page');
        $response->assertSee('login-card');
    }
}

