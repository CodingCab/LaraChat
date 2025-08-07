<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TranslationKeysExistTest extends TestCase
{
    #[Test]
    public function new_translation_keys_exist_in_locale_files(): void
    {
        $keys = [
            'tax',
            'locker box code',
            'Automatically Print Courier Label',
            'Courier label will be automatically printer when all products are shipped',
            'status',
            'courier',
            'quantity ordered',
        ];

        foreach (File::files(resource_path('js/locales')) as $file) {
            $content = File::get($file->getPathname());
            $translations = json_decode($content, true);

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $translations, "{$file->getFilename()} missing key {$key}");
                $this->assertIsString($translations[$key], "Value for {$key} in {$file->getFilename()} must be a string");
            }
        }
    }
}
