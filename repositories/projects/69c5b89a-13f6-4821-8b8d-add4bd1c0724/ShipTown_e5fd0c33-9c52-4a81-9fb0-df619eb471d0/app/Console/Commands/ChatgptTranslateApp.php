<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use OpenAI\Laravel\Facades\OpenAI;

class ChatgptTranslateApp extends Command
{
    protected $signature = 'chatgpt:translate-app {targetLang? : Target language separated by comma e.g es,pl (optional)}';

    protected $description = 'Generate localization based on en.json';
    protected array $targetLangCodes = ["blank", "de", "en", "es", "fr", "ga", "hr", "id", "it", "pl", "pt"];

    public function handle(): void
    {
        collect([
            base_path('resources/js/locales'),
            storage_path('elevenlabs/translations')
        ])
        ->each(function ($dirPath) {
            $this->translateFiles($dirPath);
        });
    }

    public function translateFiles(string $dirPath): void
    {
        $sourceFileContent = collect(json_decode(file_get_contents($dirPath . '/en.json'), true));

        $destinationFiles = $this->getFiles($dirPath);

        foreach ($destinationFiles as $langFile) {
            $targetLangCode = pathinfo($langFile, PATHINFO_FILENAME);

            switch ($targetLangCode) {
                case 'en':
                    // skip origin file
                    break;
                case 'blank':
                    $blankFileContent = $sourceFileContent
                        ->map(function ($record) {
                            return "";
                        })
                        ->sortKeys();

                    $this->writeToFile($dirPath . '/blank.json', $blankFileContent->toArray());
                    $this->info("Blank file generated");
                    break;
                default:
                    $this->translateFile($langFile, $targetLangCode, $sourceFileContent);
                    break;
            }
        }
    }

    private function writeToFile($pathFile, array $content): void
    {
        $file = fopen($pathFile, 'w');
        fwrite($file, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fclose($file);
    }

    private function askChatGptToTranslate($targetLangCode, $targetLangFile): array
    {
        $systemContent = "Role: You are an expert in localizing and translating web application content focused on warehousing, order fulfillment, marketplaces, and shipping.";
        $systemContent .= "Your task is to help a programmer translate the given JSON data into several languages while ensuring the correct context and user-friendly wording for better User Experience.";
        $systemContent .= "\n\nGoals:";
        $systemContent .= "\n1. Accurate Context: Use terminology commonly employed in warehousing, shipping, and marketplaces.";
        $systemContent .= "\n2. User-Friendliness: Ensure the translations are natural, intuitive, and easy to understand.";
        $systemContent .= "\n3. Cultural Sensitivity: Be mindful of cultural and linguistic nuances in different regions.";

        $userContent0 = "Given the following JSON:";
        $userContent0 .= $targetLangFile;
        $userContent0 .= "\nTranslate it to language code '$targetLangCode'.";
        $userContent0 .= "\ndo not translate the key";
        $userContent0 .= "\nMake sure the translations are accurate for an application interface in the context of warehousing and fulfillment.";

        $userContent1 = "I want the response in exactly the same JSON format as the provided above";

        $messages = [
            [
                'role' => 'system',
                'content' => $systemContent
            ],
            [
                'role' => 'user',
                'content' => $userContent0
            ],
            [
                'role' => 'user',
                'content' => $userContent1
            ],
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => "json_object"],
            'messages' => $messages,
        ]);

        return json_decode($result->choices[0]->message->content, true);
    }

    private function getFiles($path): Collection
    {
        $targetLangCodes = $this->targetLangCodes;

        optional($this->argument('targetLang'), function ($targetLang) use (&$targetLangCodes) {
            $targetLangCodes = explode(',', $targetLang);
        });

        $files = collect($targetLangCodes)
            ->map(function ($lang) use ($path) {
                return $path . '/' . $lang . '.json';
            });

        return collect($files);
    }

    public function translateFile($langFile, string $targetLangCode, Collection $blankFileContent): array
    {
        if (!File::exists($langFile)) {
            $this->writeToFile($langFile, []);
            $this->info("$targetLangCode.json created");
        }

        $this->info("Translating $langFile");
        $targetFileTextCollection = collect(json_decode(file_get_contents($langFile), true));

        foreach ($blankFileContent as $key => $value) {
            if (data_get($targetFileTextCollection, $key) === null) {
                $targetFileTextCollection[$key] = "";
            }
        }

        $translatedText = [];

        // translate using chunk
        $targetFileTextCollection = $targetFileTextCollection
            ->filter(function ($record) {
                if (! is_string($record)) {
                    return false;
                }
                return trim($record) === '';
            });

        $targetFileTextCollection
            ->chunk(50)
            ->each(function ($chunk) use ($targetLangCode, &$translatedText) {
                $this->info("Translating chunk..." . $chunk->count());
                ray($chunk->toArray());
                $translated = $this->askChatGptToTranslate($targetLangCode, json_encode($chunk->toArray()));
                $translatedText = array_merge($translatedText, $translated);
                $this->info("Chunk translated");
            });

        $mergedContent = $targetFileTextCollection->merge($translatedText);

        $this->info($langFile);
        $this->writeToFile($langFile, collect($mergedContent)->sortKeys()->toArray());
        $this->info("File $targetLangCode.json fully translated");

        return $translatedText;
    }
}
