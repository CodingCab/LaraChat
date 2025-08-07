<?php

namespace Tests\Modules\ElevenLabs;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Modules\ElevenLabs\src\ElevenLabsServiceProvider;
use App\Modules\ElevenLabs\src\Services\ElevenLabs;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ElevenLabsServiceProvider::enableModule();
    }

    public function testTextToSpeech()
    {
        if (!env('ELEVENLABS_API_KEY')) {
            $this->markTestSkipped('ElevenLabs API key not set');
        }

        // Text to synthesize
        $voices = collect([
            [
                'language' => 'en',
                'text' => '1 2 3 ShipTown, 1 2 3   1 2 3 Test'
            ],
//            [
//                'language' => 'en',
//                'text' => 'Hello, this is a speech test for ShipTown Voice Assistant. Click the link below to see the full list of features available in ShipTown.'
//            ],
//            [
//                'language' => 'pl',
//                'text' => 'Witaj, to jest test mowy dla Asystenta Głosowego ShipTown. Kliknij poniższy link, aby zobaczyć pełną listę dostępnych funkcjii.'
//            ],
//            [
//                'language' => 'es',
//                'text' => 'Hola, esto es una prueba de voz para el Asistente de Voz de ShipTown. Haga clic en el enlace a continuación para ver la lista completa de funciones disponibles en ShipTown.',
//            ],
//            [
//                'language' => 'hr',
//                'text' => 'Bok, ovo je test govora za glasovnog asistenta ShipTown. Kliknite na donju poveznicu da biste vidjeli potpisani popis značajki dostupnih u ShipTownu.'
//            ],
//            [
//                'language' => 'ga',
//                'text' => 'Dia dhuit, seo tástáil cainte do Chabhróir Guth ShipTown. Cliceáil ar an nasc thíos chun an liosta iomlán gnéithe atá ar fáil in ShipTown a fheiceáil.'
//            ]
        ]);

        $voices->each(function ($voice) {
            $fileName = ElevenLabs::textToSpeech($voice['language'], $voice['text']);

            $this->assertFileExists($fileName);
        });

        $this->assertTrue(true, 'Errors encountered while synthesizing text to speech');
    }

    #[Test]
    public function testIfNoErrorsDuringEvents()
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}
