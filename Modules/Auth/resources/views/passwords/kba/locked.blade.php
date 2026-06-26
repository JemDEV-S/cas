@extends('layouts.guest')

@section('title', 'Recuperación no disponible')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-14 w-14 flex items-center justify-center rounded-2xl bg-red-100 shadow-lg">
                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-2xl font-bold text-gray-900">
                No pudimos verificar tu identidad
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                El intento de recuperación expiró o se agotaron los intentos permitidos.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 space-y-4">
            <p class="text-sm text-gray-700">
                Puedes intentarlo nuevamente más tarde o comunicarte con la
                <strong>Oficina de Tecnologías de la Información</strong> para asistencia personalizada.
            </p>

            <a href="https://wa.me/51971581917" target="_blank" rel="noopener noreferrer"
               class="inline-flex w-full items-center justify-center space-x-2 px-4 py-3 bg-[#25D366] text-white rounded-xl font-semibold hover:bg-[#1da851] transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                </svg>
                <span>Contactar por WhatsApp: 971 581 917</span>
            </a>

            <a href="{{ route('password.recover.start') }}" class="block text-center text-sm font-medium text-[#3484A5] hover:text-[#2CA792]">
                Intentar nuevamente
            </a>
            <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-gray-500 hover:text-gray-700">
                Volver a iniciar sesión
            </a>
        </div>
    </div>
</div>
@endsection
