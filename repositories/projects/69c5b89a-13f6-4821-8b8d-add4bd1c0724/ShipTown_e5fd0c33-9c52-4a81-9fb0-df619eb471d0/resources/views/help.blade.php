<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: left;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="links">
            <a href="https://forge.laravel.com" target="_blank">Forge</a>
            <a href="https://github.com/codingcab/management.products.api" target="_blank">GitHub</a>

            <a href='login'>Login</a>

            <br>
            <br>
            <b>Repository:</b> CodingCabProduction/management.products.api <br>
            <b>Branch:</b> master <br>

            <ul>
                <li class="no-gutters"><b><a href="https://dash.cloudflare.com/login" target="_blank">Cloudflare</a></b></li>
                <li><input type="checkbox"> Add domain to Cloudflare</li>
                <li><b>FORGE: FRONTEND SERVER</b></li>
                <li><input type="checkbox">Create website on </li>
                <li><input type="checkbox">Install git repo<br></li>
                <li><input type="checkbox">Update Environment variables<br></li>
                <li><input type="checkbox">Run command:  php artisan key:generate --force<br></li>
                <li><input type="checkbox">Create SSL cert</li>
                <li><input type="checkbox">Enable Quick deploy</li>
                <li><b>FORGE: WORKER SERVER</b></li>
                <li><input type="checkbox">Create website</li>
                <li><input type="checkbox">Install git repo<br></li>
                <li><input type="checkbox">Update Environment variables<br></li>
                <li><input type="checkbox">Run deploy<br></li>
                <li><input type="checkbox">Run command:  php artisan key:generate --force<br></li>
                <li><input type="checkbox">Run command:  php artisan passport:keys --force<br></li>
                <li><input type="checkbox">Create CRON job <br>
                    <b>Command:</b> php7.4 /home/forge/demo.products.management/artisan schedule:run <br>
                    <b>Domain:</b> update domain in command above <br>
                    <b>User:</b> forge <br>
                    <b>Frequency:</b> Every Minute <br>
                </li>
                <li></li>
            </ul>

            <div>
                git pull origin $FORGE_SITE_BRANCH

                $FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader

                ( flock -w 10 9 || exit 1
                echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

                if [ -f artisan ]; then
                $FORGE_PHP artisan migrate --force
                fi
            </div>
        </div>
    </body>
</html>
