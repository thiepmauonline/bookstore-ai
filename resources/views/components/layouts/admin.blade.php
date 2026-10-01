<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bookstore AI admin dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard | Bookstore AI' }}</title>

    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}?v={{ filemtime(public_path('assets/admin/css/style.css')) }}">

    @livewireStyles
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        @persist('admin-sidebar')
            <x-layouts.admin.sidebar />
        @endpersist

        <div class="admin-main">
            <x-layouts.admin.topbar />

            <main class="dashboard-content">
                {{ $slot }}
            </main>

            <footer class="admin-footer">
                <div class="container-fluid px-3 px-lg-4">
                    <span>Copyright 2026 Bookstore AI.</span>
                    <span>Hệ thống Quản trị viên.</span>
                </div>
            </footer>
        </div>
    </div>

    <script data-navigate-once src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script data-navigate-once src="{{ asset('assets/admin/js/main.js') }}?v={{ filemtime(public_path('assets/admin/js/main.js')) }}"></script>
    
    @livewireScripts
</body>
</html>
