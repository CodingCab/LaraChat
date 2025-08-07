<?php

use App\Http\Middleware\AddHeaderAccessToken;
use App\Http\Middleware\CheckApiPermissions;
use App\Http\Middleware\CheckWebPermissions;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TwoFactor;
use App\Http\Middleware\BackendLocale;
use Aws\Laravel\AwsServiceProvider;
use Barryvdh\DomPDF\ServiceProvider as DomPDFServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Milon\Barcode\BarcodeServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AwsServiceProvider::class,
        SentryServiceProvider::class,
        DomPDFServiceProvider::class,
        BarcodeServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo('/dashboard');
        $middleware->append(AddHeaderAccessToken::class);

        $middleware->web([
            AuthenticateSession::class,
            CreateFreshApiToken::class,
            CheckWebPermissions::class,
            BackendLocale::class,
        ]);

        $middleware->api([
            'auth:api',
            SubstituteBindings::class,
            CheckApiPermissions::class,
            BackendLocale::class,
        ]);

        $middleware->encryptCookies(['backend_locale']);

        $middleware->throttleApi('240,1');

        $middleware->alias([
            'bindings' => SubstituteBindings::class,
            'role' => RoleMiddleware::class,
            'twofactor' => TwoFactor::class,
            'checkWebPermissions' => CheckWebPermissions::class,
            'checkApiPermissions' => CheckApiPermissions::class,
        ]);

        $middleware->priority([
            StartSession::class,
            ShareErrorsFromSession::class,
            Authenticate::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
            CheckWebPermissions::class,
            CheckApiPermissions::class,
            Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
