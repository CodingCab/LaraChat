<?php

namespace Tests\Unit;

use Tests\TestCase;

class BarcodeCommandDecodeTest extends TestCase
{
    public function test_decodes_urlencoded_shelf_command(): void
    {
        $input = 'https://myshiptown.com/?qr=shelf%3AC34';
        $text = str_replace('https://myshiptown.com/?qr=', '', $input);
        $text = urldecode($text);
        $parts = explode(':', $text);
        $this->assertSame(['shelf', 'C34'], $parts);
    }
}
