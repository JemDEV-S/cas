@extends('layouts.guest')

@section('title', 'Registrarse - Sistema CAS')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Elementos decorativos de fondo -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-32 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-32 w-80 h-80 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse delay-500"></div>
    </div>

    <div class="max-w-2xl w-full space-y-8 relative z-10">
        <!-- Header Premium -->
        <div class="text-center">
            <div class="relative inline-block">
                <!-- Logo con efecto premium -->
                <div class="mx-auto h-20 w-20 flex items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <!-- Efecto de brillo -->
                <div class="absolute inset-0 rounded-2xl bg-white opacity-20 blur-md"></div>
            </div>

            <h2 class="mt-8 text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                Crear Cuenta
            </h2>
            <p class="mt-3 text-lg text-gray-600 font-medium">
                Sistema CAS - MDSJ
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Completa tus datos para registrarte en el sistema
            </p>
        </div>

        <!-- Alertas Mejoradas -->
        @if($errors->any())
        <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-2xl shadow-2xl p-6 text-white">
            <div class="flex items-start">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold mb-2">Error en el registro</h3>
                    <ul class="text-sm space-y-1">
                        @foreach($errors->all() as $error)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $error }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if(session('status'))
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-2xl p-6 text-white">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold mb-1">¡Registro exitoso!</h3>
                    <p>{{ session('status') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Card del Formulario Premium -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 overflow-hidden transform hover:shadow-3xl transition-all duration-300">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white text-center">
                    📝 Información Personal
                </h3>
            </div>

            <div class="p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-6" id="registerForm">
                    @csrf

                    <!-- Sección de Validación DNI (solo si RENIEC está habilitado) -->
                    @if(isset($reniecEnabled) && $reniecEnabled)
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-200">
                        <div class="flex items-start mb-4">
                            <svg class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-blue-800">Validación de Identidad</h4>
                                <p class="text-xs text-blue-600 mt-1">Ingresa tu DNI y código verificador para autocompletar tus datos</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Campo DNI -->
                            <div class="md:col-span-2 space-y-2">
                                <label class="block text-sm font-bold text-gray-700">
                                    DNI <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        name="dni"
                                        id="dni"
                                        maxlength="8"
                                        value="{{ old('dni') }}"
                                        required
                                        autofocus
                                        placeholder="12345678"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 @error('dni') border-red-500 @enderror"
                                    >
                                </div>
                            </div>

                            <!-- Campo Código Verificador -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700">
                                    Código <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="codigo_verificador"
                                        id="codigo_verificador"
                                        maxlength="1"
                                        value="{{ old('codigo_verificador') }}"
                                        required
                                        placeholder="0"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-center text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 @error('codigo_verificador') border-red-500 @enderror"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 text-center">Parte posterior del DNI</p>
                            </div>
                        </div>

                        <!-- Botón de Validar -->
                        <button
                            type="button"
                            id="validateDniBtn"
                            class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white rounded-xl font-bold hover:from-blue-600 hover:to-cyan-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <span class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="validateBtnText">Validar DNI</span>
                                <svg class="w-5 h-5 animate-spin hidden" id="validateSpinner" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>

                        <!-- Mensaje de validación -->
                        <div id="validationMessage" class="mt-3 hidden"></div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(!isset($reniecEnabled) || !$reniecEnabled)
                        <!-- Campo DNI (cuando RENIEC no está habilitado) -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                DNI <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="dni"
                                    id="dni"
                                    maxlength="8"
                                    value="{{ old('dni') }}"
                                    required
                                    autofocus
                                    placeholder="12345678"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('dni') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Email -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="correo@example.com"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('email') border-red-500 @enderror"
                                >
                            </div>
                        </div>
                        @else
                        <!-- Campo Email (con RENIEC habilitado) -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="correo@example.com"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('email') border-red-500 @enderror"
                                >
                            </div>
                        </div>
                        @endif

                        <!-- Campo Nombres -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="first_name"
                                    id="first_name"
                                    value="{{ old('first_name') }}"
                                    required
                                    placeholder="Juan Carlos"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('first_name') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Apellidos -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="last_name"
                                    id="last_name"
                                    value="{{ old('last_name') }}"
                                    required
                                    placeholder="Pérez García"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('last_name') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Género -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Género <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <select
                                    name="gender"
                                    id="gender"
                                    required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('gender') border-red-500 @enderror"
                                >
                                    <option value="">Seleccione...</option>
                                    <option value="MASCULINO" {{ old('gender') == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                                    <option value="FEMENINO" {{ old('gender') == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campo Fecha de Nacimiento -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Fecha de Nacimiento <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    type="date"
                                    name="birth_date"
                                    id="birth_date"
                                    value="{{ old('birth_date') }}"
                                    required
                                    max="{{ date('Y-m-d') }}"
                                    min="1900-01-01"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('birth_date') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Dirección -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Dirección <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="address"
                                    id="address"
                                    value="{{ old('address') }}"
                                    required
                                    placeholder="Av. Principal 123"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('address') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Distrito -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Distrito <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="district"
                                    id="district"
                                    value="{{ old('district') }}"
                                    required
                                    placeholder="San Jerónimo"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('district') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Provincia -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Provincia <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="province"
                                    id="province"
                                    value="{{ old('province') }}"
                                    required
                                    placeholder="Cusco"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('province') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Departamento -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Departamento <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="department"
                                    id="department"
                                    value="{{ old('department') }}"
                                    required
                                    placeholder="Cusco"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('department') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Teléfono -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    maxlength="9"
                                    value="{{ old('phone') }}"
                                    required
                                    placeholder="987654321"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('phone') border-red-500 @enderror"
                                >
                            </div>
                        </div>
                        <!-- Campo Contraseña -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 @error('password') border-red-500 @enderror"
                                >
                            </div>
                        </div>

                        <!-- Campo Confirmar Contraseña -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">
                                Confirmar Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    required
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contraseña Mejorada -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border-2 border-green-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-bold text-green-800 mb-2">Requisitos de Contraseña</h4>
                                <ul class="text-sm text-green-700 space-y-1">
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Mínimo 8 caracteres
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Letras y números
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Caracteres especiales recomendados
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Registro -->
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold hover:from-green-600 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        <span class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>Crear Cuenta</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Link de Login Mejorado -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="font-bold text-green-600 hover:text-green-500 transition-colors duration-200 group">
                    Inicia sesión aquí
                    <svg class="w-4 h-4 inline ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </p>
        </div>

        <!-- Información adicional -->
        <div class="text-center">
            <div class="inline-flex items-center space-x-4 text-xs text-gray-500 bg-white/50 backdrop-blur-sm rounded-2xl px-4 py-3 border border-white/20">
                <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Seguro</span>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Rápido</span>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <span>Confiable</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animaciones suaves para los inputs */
    input:focus {
        transform: translateY(-1px);
    }

    /* Efecto de carga para el botón */
    button:active {
        transform: scale(0.98);
    }

    /* Validación visual en tiempo real */
    input:valid:not(:placeholder-shown) {
        border-color: #10b981;
    }

    input:invalid:not(:placeholder-shown) {
        border-color: #ef4444;
    }

    /* Animación de pulsación */
    @keyframes pulse-border {
        0%, 100% {
            border-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        50% {
            border-color: #059669;
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
    }

    .validated-input {
        animation: pulse-border 0.6s ease-out;
    }
</style>

<!-- Meta tag para CSRF token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reniecEnabled = {{ isset($reniecEnabled) && $reniecEnabled ? 'true' : 'false' }};

        // Validación en tiempo real para DNI (solo números)
        const dniInput = document.querySelector('input[name="dni"]');
        if (dniInput) {
            dniInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Validación para código verificador (solo números y letras)
        const codigoInput = document.querySelector('input[name="codigo_verificador"]');
        if (codigoInput) {
            codigoInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9A-Za-z]/g, '').toUpperCase();
            });
        }

        // Validación para teléfono (solo números)
        const phoneInput = document.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Convertir a mayúsculas automáticamente
        const uppercaseFields = ['first_name', 'last_name', 'address', 'district', 'province', 'department'];
        uppercaseFields.forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('input', function(e) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);
                });
            }
        });

        // Efectos de focus para todos los inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-green-200', 'rounded-xl');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-green-200', 'rounded-xl');
            });
        });

        // Verificación de fortaleza de contraseña
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput) {
            passwordInput.addEventListener('input', function(e) {
                updatePasswordStrength(this.value);
            });
        }

        function updatePasswordStrength(password) {
            let indicator = document.getElementById('password-strength');

            if (!indicator && password.length > 0) {
                indicator = document.createElement('div');
                indicator.id = 'password-strength';
                indicator.className = 'mt-2 text-xs font-medium';
                passwordInput.parentElement.appendChild(indicator);
            }

            if (!indicator || password.length === 0) return;

            let strength = 0;
            let feedback = '';

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    indicator.className = 'mt-2 text-xs font-medium text-red-600';
                    feedback = '❌ Contraseña débil';
                    break;
                case 2:
                    indicator.className = 'mt-2 text-xs font-medium text-yellow-600';
                    feedback = '⚠️ Contraseña media';
                    break;
                case 3:
                    indicator.className = 'mt-2 text-xs font-medium text-blue-600';
                    feedback = '👍 Contraseña buena';
                    break;
                case 4:
                    indicator.className = 'mt-2 text-xs font-medium text-green-600';
                    feedback = '✅ Contraseña fuerte';
                    break;
            }

            indicator.textContent = feedback;
        }

        // ========================================
        // VALIDACIÓN RENIEC con AJAX
        // ========================================
        if (reniecEnabled) {
            const validateBtn = document.getElementById('validateDniBtn');
            const validateBtnText = document.getElementById('validateBtnText');
            const validateSpinner = document.getElementById('validateSpinner');
            const validationMessage = document.getElementById('validationMessage');
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const genderInput = document.getElementById('gender');
            const birthDateInput = document.getElementById('birth_date');
            const form = document.getElementById('registerForm');

            let dniValidated = false;

            // Validar DNI con AJAX
            if (validateBtn) {
                validateBtn.addEventListener('click', async function(e) {
                    e.preventDefault();

                    const dni = dniInput.value;
                    const codigoVerificador = codigoInput ? codigoInput.value : '';

                    // Validar campos
                    if (!dni || dni.length !== 8) {
                        showValidationMessage('error', 'Por favor, ingrese un DNI válido de 8 dígitos');
                        return;
                    }

                    if (codigoInput && !codigoVerificador) {
                        showValidationMessage('error', 'Por favor, ingrese el código verificador');
                        return;
                    }

                    // Mostrar loading
                    validateBtn.disabled = true;
                    validateBtnText.textContent = 'Validando...';
                    validateSpinner.classList.remove('hidden');
                    validationMessage.classList.add('hidden');

                    try {
                        // Llamar a la API usando la ruta de Laravel
                        const response = await fetch('{{ route('api.auth.validate-dni') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                dni: dni,
                                codigo_verificador: codigoVerificador
                            })
                        });

                        const data = await response.json();

                        if (data.success && data.data) {
                            // Autocompletar campos
                            firstNameInput.value = data.data.first_name;
                            lastNameInput.value = data.data.last_name;

                            // Autocompletar género si está disponible
                            if (data.data.gender) {
                                genderInput.value = data.data.gender.toUpperCase();
                            }

                            // Autocompletar fecha de nacimiento si está disponible
                            if (data.data.birth_date) {
                                birthDateInput.value = data.data.birth_date;
                            }

                            // Marcar como validado
                            dniValidated = true;

                            // Agregar efecto visual
                            firstNameInput.classList.add('validated-input');
                            lastNameInput.classList.add('validated-input');
                            if (data.data.gender) {
                                genderInput.classList.add('validated-input');
                            }
                            if (data.data.birth_date) {
                                birthDateInput.classList.add('validated-input');
                            }

                            // Mostrar mensaje de éxito
                            showValidationMessage('success', '✅ DNI validado correctamente. Datos autocompletados.');

                            // Deshabilitar campos de DNI
                            dniInput.readOnly = true;
                            if (codigoInput) codigoInput.readOnly = true;

                            // Cambiar botón
                            validateBtnText.textContent = 'DNI Validado';
                            validateBtn.classList.remove('from-blue-500', 'to-cyan-600');
                            validateBtn.classList.add('from-green-500', 'to-emerald-600');

                            // Focus en email
                            document.getElementById('email').focus();
                        } else {
                            showValidationMessage('error', data.message || 'No se pudo validar el DNI');
                            dniValidated = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showValidationMessage('error', 'Error al conectar con el servicio de validación. Intente nuevamente.');
                        dniValidated = false;
                    } finally {
                        validateBtn.disabled = false;
                        if (!dniValidated) {
                            validateBtnText.textContent = 'Validar DNI';
                        }
                        validateSpinner.classList.add('hidden');
                    }
                });
            }

            // Prevenir envío si DNI no está validado
            form.addEventListener('submit', function(e) {
                if (reniecEnabled && !dniValidated) {
                    e.preventDefault();
                    showValidationMessage('error', 'Por favor, valide su DNI antes de continuar con el registro');
                    if (validateBtn) {
                        validateBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            });

            function showValidationMessage(type, message) {
                validationMessage.classList.remove('hidden');

                if (type === 'success') {
                    validationMessage.className = 'mt-3 p-4 bg-green-100 border-2 border-green-300 rounded-xl text-green-800 text-sm font-medium flex items-center';
                    validationMessage.innerHTML = `
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>${message}</span>
                    `;
                } else {
                    validationMessage.className = 'mt-3 p-4 bg-red-100 border-2 border-red-300 rounded-xl text-red-800 text-sm font-medium flex items-center';
                    validationMessage.innerHTML = `
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>${message}</span>
                    `;
                }
            }
        }
    });
</script>

@endsection
