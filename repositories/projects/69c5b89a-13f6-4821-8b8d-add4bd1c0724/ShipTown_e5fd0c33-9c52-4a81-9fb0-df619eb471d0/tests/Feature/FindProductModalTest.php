<?php
namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindProductModalTest extends TestCase
{
    #[Test]
    public function show_find_product_modal_invokes_callback_once(): void
    {
        $content = file_get_contents(base_path('resources/js/plugins/Modals.js'));
        $this->assertStringContainsString('let called = false', $content);
        $this->assertStringContainsString('if (called)', $content);
        $this->assertStringContainsString('called = true', $content);
    }
}

