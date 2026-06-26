@extends('layouts.guest')

@section('title', 'Establecer Nueva Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-14 w-14 flex items-center justify-center rounded-2xl bg-gradient-to-br from-[#2CA792] to-[#F0C84F] shadow-lg">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-2xl font-bold text-gray-900">
                Verificación exitosa
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Establece tu nueva contraseña. Mínimo 8 caracteres.
            </p>
        </div>

        @include('layouts.partials.alerts')

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form method="POST" action="{{ route('password.recover.reset.submit') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva contraseña</label>
                    <input type="password" name="password" required minlength="8" autofocus
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-mdsj-primary text-white rounded-xl font-bold hover:shadow-xl transition-all duration-300">
                    Guardar contraseña
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
