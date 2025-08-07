<?php

namespace Tests\Unit;

use Tests\TestCase;

class UnitPriceFormatterTest extends TestCase
{
    public function test_it_formats_unit_price_with_two_decimals()
    {
        $this->assertSame('4.09', $this->formatUnitPrice(4.09));
        $this->assertSame('4.90', $this->formatUnitPrice(4.9));
        $this->assertSame('5.00', $this->formatUnitPrice(5));
    }

    private function formatUnitPrice(float $price): string
    {
        $integer = floor($price);
        $decimal = str_pad((string)round(($price - $integer) * 100), 2, '0', STR_PAD_LEFT);
        return $integer . '.' . $decimal;
    }
}
