<?php

namespace Tests\Feature;

use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Error419RedirectTest extends TestCase
{
    #[Test]
    public function view_contains_redirect_script(): void
    {
        (new RegisterErrorViewPaths)();
        $html = view('errors::419')->render();
        $this->assertStringContainsString("window.location.replace('/dashboard')", $html);
    }
}
