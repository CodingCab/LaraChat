<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AppGenerateDuskTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-dusk-tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates dusk tests for all not test covered routes';

    /**
     * Command will not override existing files
     * It will only add new if do not exists.
     */
    public function handle(): int
    {
        $this->generateDuskTestsFiles();

        return 0;
    }

    private function generateDuskTestsFiles(): void
    {
        Artisan::call('route:list --json --env=production');

        $routes = collect(json_decode(Artisan::output()))
            ->filter(function ($route) {
                $isNotApiRoute = ! Str::startsWith($route->uri, 'api');
                $isGetMethod = $route->method === 'GET|HEAD';
                $isNotDevRoute = ! Str::startsWith($route->uri, '_');

                return $isNotApiRoute && $isGetMethod && $isNotDevRoute;
            });

        $routes->each(function ($route) {
            $testName = $this->getWebRouteTestName($route);
            $this->comment($testName);
            Artisan::call('app:make-dusk-test '.$testName.' --uri='.$route->uri);
            $this->info(Artisan::output());
        });
    }

    private function getWebRouteTestName($route): string
    {
        $uri = Str::title($route->uri);
        $routeName = 'Routes/'.$uri.'PageTest';

        $routeName = str_replace('-', '', $routeName);
        $routeName = str_replace('_', '', $routeName);
        $routeName = str_replace('.', '', $routeName);
        $routeName = str_replace('{', '', $routeName);
        $routeName = str_replace('}', '', $routeName);

        return implode('/', collect(explode('/', $routeName))->map(function ($part) {
            return Str::ucfirst($part);
        })->toArray());
    }
}
