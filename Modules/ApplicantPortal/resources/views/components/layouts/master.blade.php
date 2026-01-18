<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Postulante') - Municipalidad Distrital de San Jerónimo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Patrón decorativo peruano */
        .pattern-bg {
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(44, 167, 146, 0.03) 10px, rgba(44, 167, 146, 0.03) 20px),
                repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(52, 132, 165, 0.03) 10px, rgba(52, 132, 165, 0.03) 20px);
        }
        
        /* Animaciones */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        .wave-animation {
            animation: wave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
        }
        
        /* Colores personalizados */
        .bg-municipal-blue { background-color: #3484A5; }
        .bg-municipal-green { background-color: #2CA792; }
        .bg-municipal-yellow { background-color: #F0C84F; }
        .text-municipal-blue { color: #3484A5; }
        .text-municipal-green { color: #2CA792; }
        .text-municipal-yellow { color: #F0C84F; }
        .border-municipal-blue { border-color: #3484A5; }
        .border-municipal-green { border-color: #2CA792; }
        
        /* Gradientes */
        .gradient-municipal {
            background: linear-gradient(135deg, #3484A5 0%, #2CA792 100%);
        }
        
        .gradient-municipal-soft {
            background: linear-gradient(135deg, rgba(52, 132, 165, 0.1) 0%, rgba(44, 167, 146, 0.1) 100%);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 pattern-bg min-h-screen">
    <div class="min-h-screen">
        <!-- Header con logo -->
        <header class="bg-white shadow-sm border-b-4 border-municipal-green sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo y nombre -->
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 gradient-municipal rounded-xl flex items-center justify-center shadow-lg">
                            <!-- Logo placeholder - reemplazar con logo real -->
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">Municipalidad Distrital</h1>
                            <p class="text-sm text-municipal-blue font-semibold">San Jerónimo - Cusco</p>
                        </div>
                    </div>
                    
                    <!-- Navegación -->
                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('applicant.dashboard') }}" class="text-gray-700 hover:text-municipal-blue font-medium transition-colors">Inicio</a>
                        <a href="{{ route('applicant.job-postings.index') }}" class="text-gray-700 hover:text-municipal-blue font-medium transition-colors">Convocatorias</a>
                        <a href="{{ route('applicant.applications.index') }}" class="text-gray-700 hover:text-municipal-blue font-medium transition-colors">Mis Postulaciones</a>
                        <a href="#" class="text-gray-700 hover:text-municipal-blue font-medium transition-colors">Perfil</a>
                    </nav>
                    
                    <!-- Usuario -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->getFullNameAttribute() ?? 'Usuario' }}</p>
                            <p class="text-xs text-gray-500">Postulante</p>
                        </div>
                        <div class="w-10 h-10 gradient-municipal rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->getFullNameAttribute() ?? 'U', 0, 1)) }}
                        </div>
                        <!-- Botón de cerrar sesión -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors shadow-sm" title="Cerrar Sesión">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="hidden md:inline">Salir</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Mensajes de sesión -->
                @if (session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg shadow-sm" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-lg shadow-sm" role="alert">
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
        
        <!-- Footer -->
        <footer class="bg-white border-t-4 border-municipal-blue mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Municipalidad Distrital de San Jerónimo</h3>
                        <p class="text-sm text-gray-600">Trabajando juntos por el desarrollo</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Contacto</h3>
                        <p class="text-sm text-gray-600">📞 971581917</p>
                        <p class="text-sm text-gray-600">✉️ oti.@munisanjeronimo.gob.pe</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Horario de Atención</h3>
                        <p class="text-sm text-gray-600">Lunes a Viernes</p>
                        <p class="text-sm text-gray-600">8:00 AM - 4:30 PM</p>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">© 2026 Municipalidad Distrital de San Jerónimo - Oficina de Tecnologías de la Información. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>