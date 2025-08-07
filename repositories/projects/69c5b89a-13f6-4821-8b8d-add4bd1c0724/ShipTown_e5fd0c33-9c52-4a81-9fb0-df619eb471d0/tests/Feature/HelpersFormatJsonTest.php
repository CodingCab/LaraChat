<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpersFormatJsonTest extends TestCase
{
    #[Test]
    public function helpers_js_defines_format_json(): void
    {
        $content = file_get_contents(base_path('resources/js/mixins/helpers.js'));
        $this->assertStringContainsString('formatJson', $content);
    }
}
