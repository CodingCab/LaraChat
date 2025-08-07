<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppGenerateTranslationText extends Command
{
    protected $signature = 'app:generate-translation-text {path? : directory or file}';

    protected $description = 'Find translation text from selected directory or file and add to en.json';

    public function handle(): void
    {
        $this->info('Generating translation text...');
        $path = $this->argument('path') ?? base_path('resources/js');

        if (is_dir($path)) {
            $files = [];

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() === 'vue') {
                    $files[] = $file->getPathname();
                }
            }
        } else {
            $files = [$path];
        }

        $translationStrings = [];
        foreach ($files as $file) {
            $this->info('File: ' . $file);

            $fileContent = file_get_contents($file);
            $translationStrings = array_merge($translationStrings, $this->extractTranslationStrings($fileContent));
        }

        $translationStrings = collect($translationStrings)->unique()->sort()->values();

        $dirPath = base_path('resources/js/locales');
        $enFileContent = file_get_contents($dirPath . '/en.json');
        $enFileArr = json_decode($enFileContent, true);

        foreach ($translationStrings as $translationString) {
            if (!isset($enFileArr[$translationString])) {
                $enFileArr[$translationString] = $translationString;
            }
        }

        $newEnFiles = collect($enFileArr)->sortKeys();
        $newEnFileContent = json_encode($newEnFiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($dirPath . '/en.json', $newEnFileContent);
    }

    private function extractTranslationStrings($text): array
    {
        $pattern1 = '/(?<![A-Za-z0-9_])t(?:c)?\(\s*["\']((?:\\\\.|[^\\\\])*?)["\'](?:\s*,\s*[^)]+)?\)/';
        $pattern2 = '/(?<![A-Za-z0-9_])t(?:c)?\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*[^)]+)?\)/';

        preg_match_all($pattern1, $text, $matches1);
        preg_match_all($pattern2, $text, $matches2);

        $matches = array_merge($matches1[1], $matches2[1]);

        return $matches;
    }
}
