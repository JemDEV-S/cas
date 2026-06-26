@extends('layouts.guest')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-14 w-14 flex items-center justify-center rounded-2xl bg-gradient-to-br from-[#3484A5] to-[#2CA792] shadow-lg">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu DNI. Te haremos 3 preguntas basadas en los datos de tu hoja de vida para verificar tu identidad.
            </p>
        </div>

        @include('layouts.partials.alerts')

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form method="POST" action="{{ route('password.recover.start.submit') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                    <input type="text" name="dni" inputmode="numeric" maxlength="8" pattern="\d{8}"
                           value="{{ old('dni') }}" required autofocus
                           placeholder="Ingresa tu DNI (8 dígitos)"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent @error('dni') border-red-500 @enderror">
                    @error('dni')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @php $captcha = session('kba_captcha'); @endphp
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Verificación: ¿Cuánto es {{ $captcha['question'] ?? '...' }}?
                    </label>
                    <input type="number" name="captcha" required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent @error('captcha') border-red-500 @enderror">
                    @error('captcha')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-mdsj-primary text-white rounded-xl font-bold hover:shadow-xl transition-all duration-300">
                    Continuar
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-[#3484A5] hover:text-[#2CA792]">
                        ← Volver a iniciar sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
