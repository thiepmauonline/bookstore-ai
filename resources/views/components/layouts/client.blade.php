<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>{{ $title ?? 'Bookstore AI' }}</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/client/css/normalize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/client/icomoon/icomoon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/client/css/vendor.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/client/style.css') }}">

    @livewireStyles
</head>
<body data-bs-spy="scroll" data-bs-target="#header" tabindex="0">

    <x-layouts.client.header />

    <main>
        {{ $slot }}
    </main>

    <x-layouts.client.footer />

    <script src="{{ asset('assets/client/js/jquery-1.11.0.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/client/js/plugins.js') }}"></script>
    <script src="{{ asset('assets/client/js/script.js') }}"></script>

    @livewireScripts
</body>
</html>
