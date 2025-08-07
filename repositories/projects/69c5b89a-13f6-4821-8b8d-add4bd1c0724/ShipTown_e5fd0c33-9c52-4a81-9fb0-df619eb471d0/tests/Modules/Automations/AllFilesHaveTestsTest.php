<?php

namespace Tests\Modules\Automations;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class AllFilesHaveTestsTest extends TestCase
{
    public function test_all_actions_and_conditions_have_tests(): void
    {
        $sourceDirs = [
            base_path('app/Modules/Automations/src/Actions'),
            base_path('app/Modules/Automations/src/Conditions'),
        ];

        $testDir = base_path('tests/Modules/Automations');

        $testContents = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $testContents[] = file_get_contents($file->getPathname());
            }
        }

        $missing = [];
        foreach ($sourceDirs as $dir) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $class = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    $found = false;
                    foreach ($testContents as $content) {
                        if (str_contains($content, $class)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $missing[] = $class;
                    }
                }
            }
        }

        if (!empty($missing)) {
            $this->markTestIncomplete('Missing tests for: ' . implode(', ', $missing));
        }

        $this->assertEmpty($missing);
    }
}
