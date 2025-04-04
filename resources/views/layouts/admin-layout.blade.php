<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Admin Dashboard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased h-full bg-background text-foreground">
    <div x-data="{ 
        darkMode: localStorage.getItem('darkMode') === 'true',
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            document.documentElement.classList.toggle('dark', this.darkMode);
        }
    }" 
    x-init="$watch('darkMode', val => document.documentElement.classList.toggle('dark', val)); 
    document.documentElement.classList.toggle('dark', darkMode)"
    class="min-h-screen flex">

        <!-- Sidebar (Always Present) -->
        <x-admin-sidebar />

        <div class="flex-1">
            <!-- Top Navigation (Always Present) -->
            <x-admin-topnav />

            <div class="container mx-auto p-6 max-w-7xl">
                <main class="w-full">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</body>
</html>
