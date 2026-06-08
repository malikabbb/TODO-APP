<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TaskFlow - Modern task management for productive people">
    <title>{{ config('app.name', 'TaskFlow') }} – @yield('title', 'Dashboard')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">




    <script>
        (function () {
            var theme = null;

            try {
                theme = localStorage.getItem('theme');
            } catch (error) {
                theme = null;
            }

            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.dataset.theme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>

<div class="app-layout">
    {{-- Sidebar --}}
    @livewire('sidebar')

    {{-- Main --}}
    <div class="main-content">
        {{-- Navbar --}}
        @livewire('navbar')

        {{-- Task Form Modal (global) --}}
        @livewire('task-form')

        {{-- Page --}}
        <div class="page-body">
            @yield('content')
        </div>
    </div>
</div>

@livewireScripts
<script>
    document.addEventListener('notify', event => {
        alert(event.detail.message);
    });
</script>
</body>
</html>
