@extends('layouts.guest')

@section('title', 'Pregunta de Verificación')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="text-center text-2xl font-bold text-gray-900">
                Pregunta {{ $questionNumber }} de {{ $totalQuestions }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Responde con la información que registraste en tu hoja de vida.
            </p>
        </div>

        @include('layouts.partials.alerts')

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="mb-6">
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                    <div class="bg-gradient-to-r from-[#3484A5] to-[#2CA792] h-2 rounded-full transition-all duration-500"
                         style="width: {{ ($questionNumber / $totalQuestions) * 100 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 text-right">Intentos restantes: {{ $challenge->attemptsRemaining() }}</p>
            </div>

            <form method="POST" action="{{ route('password.recover.answer') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-3">
                        {{ $question['prompt'] }}
                    </label>
                    <input type="text" name="answer" required autofocus autocomplete="off" maxlength="200"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3484A5] focus:border-transparent @error('answer') border-red-500 @enderror">
                    @error('answer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-mdsj-primary text-white rounded-xl font-bold hover:shadow-xl transition-all duration-300">
                    Verificar respuesta
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-500">
            Si fallas todos los intentos deberás iniciar el proceso de nuevo.
        </p>
    </div>
</div>
@endsection
