<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @auth
        <meta name="user-id" content="{{ Auth::user()->id }}">
        <meta name="current-user" content="{{ \App\Http\Resources\UserResource::make(Auth::user()->load('warehouse', 'roles'))->toJson() }}">
    @endauth

    <title>ST @yield('title')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..700;1,200..700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('css')

    <link rel="manifest" href="{{ route('manifest.json') }}">
{{--    <script src="https://unpkg.com/html5-qrcode"></script>--}}
</head>
<body>
<div id="app">
    @if (session('alert-info-message'))
        <div class="alert alert-info" role="alert">{{ session('alert-info-message') }}</div>
    @endif

    @if (session('alert-success-message'))
        <div class="alert alert-success" role="alert">{{ session('alert-success-message') }}</div>
    @endif

    @if (session('alert-warning-message'))
        <div class="alert alert-warning" role="alert">{{ session('alert-warning-message') }}</div>
    @endif

    @if (session('alert-danger-message'))
        <div class="alert alert-danger" role="alert">{{ session('alert-danger-message') }}</div>
    @endif

    @yield('app-content')
</div>
</body>
</html>
