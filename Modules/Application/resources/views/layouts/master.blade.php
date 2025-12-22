<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Postulante') - {{ config('app.name', 'Sistema CAS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="min-h-screen">
        <!-- Navigation -->
        @include('application::components.navigation')
        
        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Mensajes de sesión -->
                @if (session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>