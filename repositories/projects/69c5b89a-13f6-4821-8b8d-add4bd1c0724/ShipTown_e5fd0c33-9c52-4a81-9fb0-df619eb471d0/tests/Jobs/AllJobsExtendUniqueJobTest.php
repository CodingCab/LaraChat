<?php

namespace Tests\Jobs;

use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class AllJobsExtendUniqueJobTest extends TestCase
{
    #[Test]
    public function all_jobs_extend_unique_job(): void
    {
        foreach ($this->getJobFiles() as $jobFile) {
            if (basename($jobFile) === 'UniqueJob.php') {
                continue;
            }

            $class = $this->getClassFromPath($jobFile);

            if (!class_exists($class)) {
                require_once $jobFile;
            }

            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isSubclassOf(\App\Abstracts\UniqueJob::class),
                $class.' must extend '.\App\Abstracts\UniqueJob::class
            );
        }
    }

    private function getJobFiles(): array
    {
        $directory = new \RecursiveDirectoryIterator(app_path('Jobs'));
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+Job\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $jobFiles = [];
        foreach ($regex as $file) {
            $jobFiles[] = $file[0];
        }

        return $jobFiles;
    }

    private function getClassFromPath(string $path): string
    {
        $relative = str_replace(app_path().DIRECTORY_SEPARATOR, '', $path);
        $class = 'App\\'.str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

        return $class;
    }
}
