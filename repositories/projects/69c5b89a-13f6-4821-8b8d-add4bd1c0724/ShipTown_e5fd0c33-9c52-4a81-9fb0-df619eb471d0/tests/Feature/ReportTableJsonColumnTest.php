<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTableJsonColumnTest extends TestCase
{
    #[Test]
    public function report_table_supports_json_column_type(): void
    {
        $content = file_get_contents(base_path('resources/js/components/Reports/ReportTable.vue'));
        $this->assertStringContainsString("field.type === 'json'", $content);
        $this->assertStringContainsString('formatJson', $content);
    }
}
