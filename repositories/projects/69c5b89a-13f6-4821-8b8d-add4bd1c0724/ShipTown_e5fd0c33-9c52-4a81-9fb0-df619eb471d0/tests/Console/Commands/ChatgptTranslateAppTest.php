<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ChatgptTranslateAppTest extends TestCase
{
    protected string $locales_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locales_path = resource_path('js/locales');

        if (!File::exists(base_path($this->locales_path))) {
            File::makeDirectory($this->locales_path, 0777, true, true);
        }

        // Rename the source file to test the blank file generation
        if (File::exists($this->locales_path . '/en.json')) {
            File::move($this->locales_path . '/en.json', $this->locales_path . '/en.json_bck');
        }
        if (File::exists($this->locales_path . '/blank.json')) {
            File::move($this->locales_path . '/blank.json', $this->locales_path . '/blank.json_bck');
        }
        if (File::exists($this->locales_path . '/es.json')) {
            File::move($this->locales_path . '/es.json', $this->locales_path . '/es.json_bck');
        }
    }

    protected function tearDown(): void
    {
        // Restore original files
        if (File::exists($this->locales_path . '/en.json_bck')) {
            File::delete($this->locales_path . '/en.json');
            File::move($this->locales_path . '/en.json_bck', $this->locales_path . '/en.json');
        }

        if (File::exists($this->locales_path . '/blank.json_bck')) {
            File::delete($this->locales_path . '/blank.json');
            File::move($this->locales_path . '/blank.json_bck', $this->locales_path . '/blank.json');
        }

        if (File::exists($this->locales_path . '/es.json_bck')) {
            File::delete($this->locales_path . '/es.json');
            File::move($this->locales_path . '/es.json_bck', $this->locales_path . '/es.json');
        }

        parent::tearDown();
    }

    public function testCommandGeneratesBlankFile()
    {
        $this->markTestSkipped('Skipping test for now');

        if (!env('OPENAI_API_KEY')) {
            $this->markTestSkipped('OPENAI_API_KEY is not set in .env file');
            return;
        }

        // Create a target language file for Spanish
        File::put($this->locales_path . '/en.json', json_encode([
            'Hello' => '',
            'Welcome' => ''
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Run the command
        $this->artisan('chatgpt:translate-app blank')
            ->expectsOutput('Blank file generated')
            ->assertExitCode(0);

        // Verify that the blank.json file has keys but empty values
        $blankContent = json_decode(File::get($this->locales_path . '/blank.json'), true);

        $this->assertEquals([
            'Hello' => '',
            'Welcome' => ''
        ], $blankContent);

    }

    public function testCommandGeneratesTranslationFile()
    {
        $this->markTestSkipped('Skipping test for now');

        if (!env('OPENAI_API_KEY')) {
            $this->markTestSkipped('OPENAI_API_KEY is not set in .env file');
            return;
        }

        // Create a target language file for Spanish
        File::put($this->locales_path . '/en.json', json_encode([
            'Hello' => 'Hello',
            'Welcome' => 'Welcome'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Run the command
        $this->artisan('chatgpt:translate-app es')
            ->assertExitCode(0);

        // Verify the translation file is populated with some content
        $translatedContent = json_decode(File::get($this->locales_path . '/es.json'), true);

        $this->assertNotEmpty($translatedContent);
        $this->assertArrayHasKey('Hello', $translatedContent);
        $this->assertArrayHasKey('Welcome', $translatedContent);

        // Optional: Verify the values are translated
        $this->assertNotEquals('', $translatedContent['Hello']);
        $this->assertNotEquals('', $translatedContent['Welcome']);
    }
}
