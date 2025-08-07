<?php

namespace App\Modules\ElevenLabs\src\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;

class ElevenLabs
{
    protected static array $voices = [
        'en' => ['voice_id' => 'bCwW7dMszE8OyqvhCaQY', 'model_id' => "eleven_multilingual_v2"],
        'pl' => ['voice_id' => 'WnbeXqPxZOBCr1psEn6Z', 'model_id' => "eleven_multilingual_v2"],
        'es' => ['voice_id' => 'tXgbXPnsMpKXkuTgvE3h', 'model_id' => "eleven_multilingual_v2"],
        'hr' => ['voice_id' => 'ZLYZToA7aDsMbHwM9AOr', 'model_id' => "eleven_multilingual_v2"],
        'id' => ['voice_id' => 'lFjzhZHq0NwTRiu2GQxy', 'model_id' => "eleven_multilingual_v2"],
        'ga' => ['voice_id' => 'ZLYZToA7aDsMbHwM9AOr', 'model_id' => "eleven_multilingual_v2"],
        'pt' => ['voice_id' => 'WgE8iWzGVoJYLb5V7l2d', 'model_id' => "eleven_multilingual_v2"], // Hugo Mendonça
        'fr' => ['voice_id' => 'ohItIVrXTBI80RrUECOD', 'model_id' => "eleven_multilingual_v2"], // Guillaume - Narration
        'de' => ['voice_id' => 'AnvlJBAqSLDzEevYr9Ap', 'model_id' => "eleven_multilingual_v2"], // Ava - youthful and expressive German
        'it' => ['voice_id' => 'PSp7S6ST9fDNXDwEzX0m', 'model_id' => "eleven_multilingual_v2"], // Alessandro
    ];

    public static function setSpeakingLanguage(string $langCode = null): void
    {
        Cache::delete('elevenlabs-language');

        Cache::put('elevenlabs-language', $langCode, now()->addHour());
    }

    private static function client(): PendingRequest
    {
        return Http::baseUrl('https://api.elevenlabs.io/')
            ->withHeaders([
                'xi-api-key' => config('elevenlabs.api_key', env('ELEVENLABS_API_KEY')),
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * @throws ConnectionException
     */
    public static function textToSpeech($langCode, $text, $autoplay = false, $await = false, $breakTime = '0.1s'): ?string
    {
        if (empty($text)) {
            return null;
        }

        if (Cache::has('elevenlabs-language') && Cache::get('elevenlabs-language') !== $langCode) {
            return null;
        }

        $lang = data_get(self::$voices, $langCode) ?? self::$voices['en'];

        $outputFileName = implode('', [
                app()->storagePath('/elevenlabs/audio/'. $langCode),
                md5($text),
                $lang['voice_id']
            ]);

        $outputFileName .= '.mp3';

        if (!file_exists($outputFileName)) {
            self::generateAudio($text, $lang['voice_id'], $outputFileName, $breakTime);
        }

        if ($autoplay) {
            self::playAudio($outputFileName, $await);
        }

        return $outputFileName;
    }

    public static function playAudio(string $filePath, bool $await = false): void
    {
        exec("mpg123 {$filePath}" . ($await ? '' : ' > /dev/null 2>&1 &'));
    }

    /**
     * @throws ConnectionException
     */
    public static function say(string $text, bool $await = true): ?string
    {
        if (empty(env('ELEVENLABS_API_KEY'))) {
            return null;
        }

        $languageCode = self::getCurrentLanguageCode();
        $translatedText = self::getTranslatedText($text, $languageCode);

        return self::textToSpeech($languageCode, $translatedText, true, $await);
    }

    public static function getTranslatedText(string $text, $languageCode = 'en'): string
    {
        if ($languageCode === 'en') {
            return $text;
        }

        $filename = 'elevenlabs/translations/' . $languageCode . '.json';

        $t = Storage::json($filename) ?? [];

        $translatedText = data_get($t, $text, $text);

        return empty(trim($translatedText)) ? $text : $translatedText;
    }

    /**
     * @return mixed|string
     */
    public static function getCurrentLanguageCode(): mixed
    {
        return Cache::get('elevenlabs-language') ?? 'en';
    }

    /**
     * @param $text
     * @param mixed $breakTime
     * @param $voice_id
     * @param string $outputFileName
     * @return void
     * @throws ConnectionException
     */
    public static function generateAudio(string $text, string $voice_id, string $outputFileName, string $breakTime = null): void
    {
        if (empty($text)) {
            return;
        }

        $finalText = $text;

        if ($breakTime) {
            $finalText = $finalText . '<break time="' . $breakTime . '" />';
        }

        $data = [
            'previous_text' => Cache::get('elevenlabs-previous_text') ?? '',
            'model_id' => 'eleven_multilingual_v2',
            'text' => $finalText,
        ];

        $response = self::client()->post('v1/text-to-speech/' . $voice_id, $data);

        Cache::put('elevenlabs-previous_text', $data['text'], now()->addSeconds(30));

        $audioStream = $response->getBody()->getContents();

        file_put_contents($outputFileName, $audioStream);
    }
}
