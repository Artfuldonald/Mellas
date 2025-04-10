<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MELLA'S ADMIN DASHBOARD</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased h-full bg-gray-100"> {{-- Example light background --}}
   
    {{-- Main Flex Container --}}
    <div class="flex min-h-screen"> {{-- Ensure flex and min-h-screen --}}

        <!-- Sidebar -->
        <x-admin-sidebar /> 

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <x-admin-topnav /> 
            <main class="flex-1 container mx-auto p-6 max-w-7xl">
                 {{ $slot }}
            </main>
        </div> {{-- End Main Content Area --}}

    </div> {{-- End Main Flex Container --}}

    @stack('scripts')
</body>
</html>