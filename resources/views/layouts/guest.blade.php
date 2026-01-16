<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema CAS') }} @hasSection('title') - @yield('title') @endif</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Patrón geométrico inspirado en arquitectura incaica */
        .geometric-pattern {
            background-color: #f8fafc;
            background-image: 
                /* Patrón de piedra trapecio (ventanas incaicas) */
                repeating-linear-gradient(0deg, transparent, transparent 35px, rgba(52, 132, 165, 0.03) 35px, rgba(52, 132, 165, 0.03) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 35px, rgba(52, 132, 165, 0.03) 35px, rgba(52, 132, 165, 0.03) 40px),
                /* Patrón de escalones (chakana simplificada) */
                linear-gradient(45deg, transparent 45%, rgba(44, 167, 146, 0.04) 45%, rgba(44, 167, 146, 0.04) 55%, transparent 55%),
                linear-gradient(-45deg, transparent 45%, rgba(44, 167, 146, 0.04) 45%, rgba(44, 167, 146, 0.04) 55%, transparent 55%),
                /* Patrón de bloques de piedra */
                repeating-linear-gradient(0deg, rgba(52, 132, 165, 0.02), rgba(52, 132, 165, 0.02) 2px, transparent 2px, transparent 80px),
                repeating-linear-gradient(90deg, rgba(52, 132, 165, 0.02), rgba(52, 132, 165, 0.02) 2px, transparent 2px, transparent 120px);
            background-size: 100% 100%, 100% 100%, 60px 60px, 60px 60px, 100% 100%, 100% 100%;
            background-position: 0 0, 0 0, 0 0, 0 0, 0 0, 0 0;
        }

        /* Decoración incaica adicional para esquinas */
        .inca-corner {
            position: relative;
        }
        
        .inca-corner::before,
        .inca-corner::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            opacity: 0.1;
            background-image: 
                linear-gradient(45deg, currentColor 25%, transparent 25%),
                linear-gradient(-45deg, currentColor 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, currentColor 75%),
                linear-gradient(-45deg, transparent 75%, currentColor 75%);
            background-size: 30px 30px;
            background-position: 0 0, 0 15px, 15px -15px, -15px 0px;
        }
        
        .inca-corner::before {
            top: 0;
            left: 0;
            color: #3484A5;
        }
        
        .inca-corner::after {
            bottom: 0;
            right: 0;
            color: #2CA792;
            transform: rotate(180deg);
        }

        /* Animaciones suaves */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(52, 132, 165, 0.3); }
            50% { box-shadow: 0 0 40px rgba(44, 167, 146, 0.5); }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .pulse-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        /* Gradientes institucionales */
        .bg-mdsj-primary {
            background: linear-gradient(135deg, #3484A5 0%, #2CA792 100%);
        }

        .bg-mdsj-accent {
            background: linear-gradient(135deg, #2CA792 0%, #F0C84F 100%);
        }

        .text-mdsj-primary {
            background: linear-gradient(135deg, #3484A5 0%, #2CA792 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Efecto glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-900">
    <div class="geometric-pattern min-h-screen">
        @yield('content')

        <!-- Footer OTI - Discreto y Profesional -->
        <footer class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm border-t border-gray-200 py-3 z-50">
            <div class="container mx-auto px-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-600">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span class="font-medium">Desarrollado por la Oficina de Tecnologías de la Información</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-500">MDSJ © {{ date('Y') }}</span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-gray-400">Sistema Activo</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>