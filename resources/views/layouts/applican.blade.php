<!DOCTYPE html>
<html lang="{{ str_replace('_'.'-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token()}}">

    <title>{{ config('app.name', 'Sistema CAS MDSJ') }} - @yield('title', 'Postulaciones')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('layouts.partials.navigation')

        <!-- Page Content -->
        <main class="px-3 sm:px-4 lg:px-5"> <!-- Padding reducido -->
            @include('layouts.partials.alerts')
            
            <!-- Contenido más compacto -->
            <div class="space-y-4 sm:space-y-5"> <!-- Espaciado vertical reducido -->
                @yield('content')
            </div>
        </main>

        @include('layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>