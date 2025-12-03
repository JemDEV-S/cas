@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-32 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-32 w-80 h-80 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse delay-500"></div>
    </div>

    <div class="max-w-lg w-full space-y-8 relative z-10">
        <div class="text-center px-4">
            <div class="relative inline-block">
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="absolute inset-0 rounded-2xl bg-white opacity-20 blur-md pointer-events-none"></div>
            </div>

            <h2 class="mt-8 text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent leading-tight">
                Sistema CAS
            </h2>
            <p class="mt-3 text-lg text-gray-600 font-medium leading-relaxed">
                Municipalidad Distrital de San Jerónimo
            </p>
            <p class="mt-2 text-base text-gray-500 leading-relaxed">
                Inicia sesión con tus credenciales
            </p>
        </div>

        <div class="space-y-4">
            @if(session('status'))
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-2xl p-5 text-white mx-4 animate-fade-in-down">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="font-medium text-sm sm:text-base">{{ session('status') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-2xl shadow-2xl p-5 text-white mx-4 animate-fade-in-down">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium mb-1 text-sm sm:text-base">Error al iniciar sesión</p>
                        <ul class="text-xs sm:text-sm space-y-1">
                            @foreach($errors->all() as $error)
                            <li class="break-words">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 overflow-hidden transform hover:shadow-3xl transition-all duration-300 mx-4">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                <h3 class="text-lg sm:text-xl font-bold text-white text-center">
                    🔐 Iniciar Sesión
                </h3>
            </div>

            <div class="p-6 sm:p-8 md:p-10">
                <form method="POST" action="{{ route('login') }}" class="space-y-6 sm:space-y-8">
                    @csrf

                    <div class="space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-gray-700">
                            DNI o Correo Electrónico
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                required
                                autofocus
                                placeholder="12345678 o correo@example.com"
                                class="w-full pl-12 pr-4 py-3 sm:py-4 text-sm sm:text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 @error('login') border-red-500 @enderror"
                            >
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-gray-700">
                            Contraseña
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-12 pr-4 py-3 sm:py-4 text-sm sm:text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 @error('password') border-red-500 @enderror"
                            >
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="remember" class="sr-only peer">
                                <div class="w-5 h-5 sm:w-6 sm:h-6 bg-gray-200 rounded border border-gray-300 peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all duration-200 flex items-center justify-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-sm sm:text-base text-gray-600 group-hover:text-gray-800 transition-colors">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm sm:text-base font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            ¿Olvidaste tu contraseña?
                        </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm sm:text-base"
                    >
                        <span class="flex items-center justify-center space-x-3">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Iniciar Sesión</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        @if (Route::has('register'))
        <div class="text-center px-4">
            <p class="text-sm sm:text-base text-gray-600">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-500 transition-colors duration-200">
                    Regístrate aquí
                </a>
            </p>
        </div>
        @endif

        <div class="text-center px-4 pb-4">
            <div class="inline-flex flex-wrap justify-center items-center gap-3 sm:gap-4 text-xs sm:text-sm text-gray-500 bg-white/50 backdrop-blur-sm rounded-2xl px-4 py-3 sm:px-6 sm:py-4 border border-white/20 max-w-md mx-auto">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Seguro</span>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Rápido</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input:focus { transform: translateY(-1px); }
    button:active { transform: scale(0.98); }
</style>
@endsection