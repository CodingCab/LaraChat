<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaravelZeroExcludedTest extends TestCase
{
    #[Test]
    public function application_class_is_loaded_from_laravel_framework(): void
    {
        $reflection = new \ReflectionClass(\Illuminate\Foundation\Application::class);
        $path = str_replace('\\', '/', $reflection->getFileName());
        $this->assertStringContainsString('/vendor/laravel/framework/', $path);
        $this->assertStringNotContainsString('/vendor/laravel-zero/foundation/', $path);
    }
}
