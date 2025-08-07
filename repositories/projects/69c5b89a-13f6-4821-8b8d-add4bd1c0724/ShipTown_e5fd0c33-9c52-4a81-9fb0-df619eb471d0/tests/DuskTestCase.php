<?php

namespace Tests;

use App\Abstracts\Browser;
use App\Console\Commands\ClearDatabaseCommand;
use App\Modules\ElevenLabs\src\Services\ElevenLabs;
use App\Modules\TestVideoRecordings\src\Services\ScreenRecordingService;
use App\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Throwable;

abstract class DuskTestCase extends BaseTestCase
{
    private Browser $browser;

    protected User $testUser;

    protected $recordingProcess;

    protected ?Carbon $recordingStartedAt = null;

    protected int $superShortDelay = 50;

    protected int $shortDelay = 300;

    protected int $mediumDelay = 500;

    protected int $longDelay = 0;

    protected int $screenshotNumber = 1;

    private ScreenRecordingService $recordingService;
    private bool $isRecording = false;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->recordingService = new ScreenRecordingService();
    }

    public function getScriptFileName(): string
    {
        return storage_path('/dusk-recordings/') . Str::replace('Tests/Browser/', '', str_replace('\\', '/', get_called_class())) . '_script.txt';
    }

    protected function setUp(): void
    {
        parent::setUp();

        ray()->showApp();

        ray()->clearAll();
        ray()->className($this)->blue();

        ClearDatabaseCommand::resetDatabase();

        Artisan::call('app:install');

        $this->testUser = User::factory()->create([
            'email' => 'demo-user@ship.town',
            'password' => bcrypt('secret1144'),
        ]);
        $this->testAdmin = User::factory()->create([
            'email' => 'demo-admin@ship.town',
            'password' => bcrypt('secret1144'),
        ]);

        $this->testUser->assignRole('user');
        $this->testAdmin->assignRole('admin');

        ElevenLabs::setSpeakingLanguage(env('ELEVENLABS_LANGUAGE', 'en'));
    }

    protected function tearDown(): void
    {
        $this->stopRecording();

        $this->browser()->quit();

        parent::tearDown();
    }

    public function visit(string $uri, ?User $user = null): self
    {
        $this->browser()
            ->loginAs($user ?? $this->testUser)
            ->visit($uri)
            ->pause($this->shortDelay)
            ->assertSourceMissing('Server Error')
            ->assertSourceMissing('snotify-error');

        return $this;
    }

    public function say(string $text): static
    {
        if (env('DUSK_RECORDING', false) && env('ENABLE_SPEACH', false)) {
            ElevenLabs::say($text);
        }

        return $this;
    }

    public function browser(): Browser
    {
        $this->browser = $this->browser ?? new Browser($this->driver());

        $this->browser->disableFitOnFailure();

        return $this->browser;
    }

    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--disable-search-engine-choice-screen',
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=300,800',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * @throws Throwable
     */
    public function basicUserAccessTest(string $uri, bool $allowed, ?User $user = null): void
    {
        /** @var User $visitor */
        if ($user) {
            $visitor = $user;
        } else {
            $visitor = User::factory()->create();
            $visitor->assignRole('user');
        }

        $this->browser()
            ->loginAs($visitor)
            ->visit($uri)
            ->pause($this->shortDelay);

        $this->browser()
            ->assertSourceMissing('Server Error')
            ->assertSourceMissing('snotify-error');

        if ($allowed) {
            $this->browser()->assertPathIs($uri);
        } else {
            $this->browser()->assertSee('Unauthorized');
        }
    }

    /**
     * @throws Throwable
     */
    public function basicAdminAccessTest(string $uri, bool $allowed): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->basicUserAccessTest($uri, $allowed, $user);
    }

    protected static function setEnvironmentValue($key, $value): void
    {
        $path = app()->environmentFilePath();

        if (! file_exists($path)) {
            return;
        }

        $str = file_get_contents($path);

        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$key}=");
        $endOfLinePosition = strpos($str, "\n", $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

        // If key does not exist, add it
        if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
            $str .= "{$key}={$value}\n";
        } else {
            $str = str_replace($oldLine, "{$key}={$value}", $str);
        }

        file_put_contents($path, $str);
    }

    protected function sendKeysTo(Browser $browser, string $keys): void
    {
        $browser->driver->getKeyboard()->sendKeys($keys);
    }

    protected function clickButton(string $selector, bool $autoScreenShot = true): static
    {
        $this->browser()
            ->waitFor($selector, 2)
            ->mouseover($selector);

        $this->browser()
            ->click($selector)
            ->pause($this->shortDelay);

        optional($this->screenshot(), fn () => $autoScreenShot);

        return $this;
    }

    protected function typeSlowly(string $field, string $text): static
    {
        $this->browser()
            ->waitFor($field)
            ->typeSlowly($field, $text)
            ->pause($this->shortDelay);

        return $this;
    }

    protected function type(?string $field = null, string $value = '', bool $enter = false): static
    {
        if ($field) {
            $this->browser()->waitFor($field);
        }

        $this->sendKeysTo($this->browser(), $value);
        $this->browser()->pause($this->shortDelay);

        if ($enter) {
            $this->clickEnter($field);
            $this->browser()->pause($this->shortDelay);
        }

        return $this;
    }

    protected function typeAndEnter(string $text, bool $performAssertions = true): static
    {
        $this->type(null, $text, true);

        $this->browser()
            ->pause($this->shortDelay);

        if ($performAssertions) {
            $this->browser()
                ->assertSourceMissing('Server Error')
                ->assertSourceMissing('snotify-error');
        }

        return $this;

    }

    protected function pause(float $seconds = 0.5): static
    {
        $this->browser()->pause($seconds * 1000);

        return $this;
    }

    public function startRecording(?string $title = null): self
    {
        if (env('DUSK_RECORDING', false) === false) {
            return $this;
        }

        $finalFilename = Str::of(get_called_class())
            ->replace('Tests\\', '')
            ->replace('Browser\Routes\\', '')
            ->replace('Routes\\', '')
            ->replace('PageTest', '')
            ->replace('Test', '')
            ->replace('\\', '/')
            ->headline();

        if ($title) {
            $finalFilename .= ' - ' . $title;
        } else {
            $parent_method = debug_backtrace()[1]['function'];

            $finalFilename .= ' - ' . Str::of($parent_method)
                    ->replaceFirst('test', '')
                    ->headline();
        }

        $finalFilename = ElevenLabs::getTranslatedText($finalFilename);

        $this->recordingService->startRecording(trim($finalFilename));
        $this->isRecording = true;

        $this->pause(2);

        return $this;
    }

    protected function stopRecording(): void
    {
        if ($this->isRecording) {
            exec('./bin/recording.sh stop');
            $this->isRecording = false;
            $this->pause();
        }
    }

    public function clearDubbingScript(): void
    {
        $filename = $this->getScriptFileName();

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function clickEnter(?string $selector = null): static
    {
        $this->screenshot();

        if ($selector) {
            $this->browser()
                ->waitFor($selector)
                ->keys($selector, '{ENTER}');
        } else {
            $this->sendKeysTo($this->browser, WebDriverKeys::ENTER);
        }

        $this->browser()->pause($this->shortDelay);

        return $this;
    }

    public function clickEscape(?string $selector = null): static
    {
        if ($selector) {
            $this->browser()
                ->waitFor($selector)
                ->keys($selector, '{ESCAPE}');
        } else {
            $this->sendKeysTo($this->browser, WebDriverKeys::ESCAPE);
        }

        $this->browser()->pause($this->shortDelay);

        return $this;
    }

    protected function waitUntilMissingText(string $string, int $seconds = 1): static
    {
        $this->browser()->waitUntilMissingText($string, $seconds);

        return $this;
    }

    protected function screenshot(): self
    {
        $filename = Str::of(get_called_class())
            ->replace('Tests\Browser\\', '')
            ->replace('Routes\\', '')
            ->replace('\\', '/')
            ->replace('Test', '');

        $this->browser()->screenshot('./../../../public/img/screenshots/'.$filename . '/' . sprintf('%03d', $this->screenshotNumber++));

        return $this;
    }
}
