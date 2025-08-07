<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PM @yield('title')</title>

    <!-- Styles -->
{{--    <link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}

<style>
*{
    font-family:"DeJaVu Sans Mono",monospace;
}
</style>
</head>

<body class="m-0">
    @yield('content')
</body>
</html>
