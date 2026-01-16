@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden pb-20">
    
    <!-- Elementos decorativos de fondo con colores institucionales -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-32 w-96 h-96 bg-[#3484A5] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-32 w-96 h-96 bg-[#2CA792] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#F0C84F] rounded-full mix-blend-multiply filter blur-3xl opacity-15 animate-pulse" style="animation-delay: 0.5s;"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10">
        
        <!-- Header con branding institucional orientado a postulantes -->
        <div class="text-center">
            <div class="relative inline-block float-animation">
                <!-- Escudo/Logo de la Municipalidad -->
                <div class="mx-auto h-28 w-28 flex items-center justify-center rounded-3xl bg-gradient-to-br from-[#3484A5] to-[#2CA792] shadow-2xl transform hover:scale-105 transition-all duration-300 pulse-glow">
                    <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <!-- Efecto de brillo sutil -->
                <div class="absolute inset-0 rounded-3xl bg-gradient-to-tr from-white/30 to-transparent opacity-50"></div>
            </div>

            <h1 class="mt-8 text-4xl font-bold text-mdsj-primary leading-tight">
                Portal CAS-MDSJ
            </h1>
            <div class="mt-4 space-y-2">
                <p class="text-sm text-gray-600">
                    Municipalidad Distrital de San Jerónimo
                </p>
            </div>
        </div>

        <!-- Alertas mejoradas -->
        <div class="space-y-4">
            @if(session('status'))
            <div class="glass-card rounded-2xl shadow-xl p-5 border-l-4 border-[#2CA792] animate-fade-in">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[#2CA792]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-800">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="glass-card rounded-2xl shadow-xl p-5 border-l-4 border-red-500 animate-fade-in">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Error al iniciar sesión</h3>
                        <ul class="text-xs text-gray-600 space-y-1">
                            @foreach($errors->all() as $error)
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>{{ $error }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Card del formulario -->
        <div class="glass-card rounded-3xl shadow-2xl overflow-hidden transform hover:shadow-3xl transition-all duration-300">
            
            <!-- Header del card -->
            <div class="bg-mdsj-primary px-8 py-6">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2 class="text-xl font-bold text-white">Iniciar Sesión</h2>
                </div>
            </div>

            <!-- Cuerpo del formulario -->
            <div class="p-8 sm:p-10">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Campo DNI o Email -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            DNI o Correo Electrónico
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#3484A5] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                required
                                autofocus
                                placeholder="12345678 o correo@ejemplo.com"
                                class="w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent transition-all duration-300 @error('login') border-red-500 @enderror"
                            >
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            Contraseña
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#3484A5] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent transition-all duration-300 @error('password') border-red-500 @enderror"
                            >
                        </div>
                    </div>

                    <!-- Recordarme y Olvidé contraseña -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <input type="checkbox" name="remember" class="w-5 h-5 text-[#3484A5] border-gray-300 rounded focus:ring-[#3484A5] transition-colors">
                            <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#3484A5] hover:text-[#2CA792] transition-colors duration-200">
                            ¿Olvidaste tu contraseña?
                        </a>
                        @endif
                    </div>

                    <!-- Botón de inicio de sesión -->
                    <button
                        type="submit"
                        class="w-full px-6 py-4 bg-mdsj-primary text-white rounded-xl font-bold hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-[#3484A5]/50 flex items-center justify-center space-x-3"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Acceder a mi cuenta</span>
                    </button>
                </form>
            </div>

            <!-- Footer del card con CTA para registro -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-8 py-6 border-t border-gray-200">
                @if (Route::has('register'))
                <div class="text-center space-y-3">
                    <p class="text-sm font-medium text-gray-700">
                        ¿Primera vez en el portal?
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#2CA792] to-[#F0C84F] text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 group w-full sm:w-auto">
                        <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span>Crear cuenta para postular</span>
                    </a>
                    <div class="flex items-center justify-center space-x-4 text-xs text-gray-600 mt-3">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-[#2CA792]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span>Ver convocatorias</span>
                        </div>
                        <div class="w-px h-4 bg-gray-300"></div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-[#3484A5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Postular a CAS</span>
                        </div>
                        <div class="w-px h-4 bg-gray-300"></div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-[#F0C84F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Seguimiento</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.5s ease-out;
    }

    input:focus {
        transform: translateY(-1px);
    }

    button:active {
        transform: scale(0.98) !important;
    }
</style>
@endsection