<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTableDropdownBoundaryTest extends TestCase
{
    #[Test]
    public function dropdown_has_viewport_boundary_attribute(): void
    {
        $content = file_get_contents(base_path('resources/js/components/Reports/ReportTable.vue'));
        $this->assertStringContainsString('data-boundary="viewport"', $content);
    }
}
