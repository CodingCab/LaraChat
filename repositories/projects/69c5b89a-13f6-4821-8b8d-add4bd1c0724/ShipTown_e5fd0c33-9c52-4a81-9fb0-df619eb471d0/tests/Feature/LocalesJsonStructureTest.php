<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalesJsonStructureTest extends TestCase
{
    #[Test]
    public function each_language_json_file_has_correct_structure(): void
    {
        $directories = [
            base_path('locales/backend'),
            resource_path('js/locales'),
        ];

        foreach ($directories as $directory) {
            foreach (File::files($directory) as $file) {
                $this->assertSame('json', $file->getExtension(), $file->getFilename() . ' must have .json extension');

                $content = File::get($file->getRealPath());
                $decoded = json_decode($content, true);

                $this->assertIsArray($decoded, $file->getFilename() . ' must decode to an associative array');

                foreach ($decoded as $key => $value) {
                    $this->assertIsString($key, 'Key ' . $key . ' in ' . $file->getFilename() . ' must be a string');
                    $this->assertIsString($value, 'Value for ' . $key . ' in ' . $file->getFilename() . ' must be a string');
                }
            }
        }
    }
}

