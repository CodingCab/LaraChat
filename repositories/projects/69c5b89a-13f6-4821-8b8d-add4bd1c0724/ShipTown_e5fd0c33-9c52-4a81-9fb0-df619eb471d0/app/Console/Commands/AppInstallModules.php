<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Modules\BaseModuleServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppInstallModules extends Command
{
    protected $signature = 'app:install-modules';

    protected $description = 'Install all modules found in app/Modules';

    public function handle(): int
    {
        $serviceProviders = collect(File::allFiles(app_path('Modules')))
            ->filter(function ($file) {
                return Str::endsWith($file->getFilename(), 'ServiceProvider.php')
                    || Str::endsWith($file->getFilename(), 'ServiceProviderBase.php');
            })
            ->map(function ($file) {
                $relative = Str::after($file->getPathname(), app_path() . DIRECTORY_SEPARATOR);
                $class = 'App\\' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

                return $class;
            });

        $installed = Module::pluck('service_provider_class')->all();

        foreach ($serviceProviders as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $class::installModule();
            $installed = array_diff($installed, [$class]);
        }

        foreach ($installed as $missing) {
            if (! class_exists($missing)) {
                Module::query()->where('service_provider_class', $missing)->delete();
            }
        }

        return Command::SUCCESS;
    }
}
