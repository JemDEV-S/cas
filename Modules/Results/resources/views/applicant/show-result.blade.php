@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">{{ $publication->phase->label() }}</h2>
            <p class="text-gray-500">{{ $publication->jobPosting->code }}</p>
        </div>
        <div>
            <a href="{{ route('applicant.results.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Resultado principal --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="md:col-span-2">
                    <h4 class="text-xl font-semibold text-gray-900 mb-4">Tu Resultado</h4>

                    @if($publication->phase->value === 'PHASE_04')
                        {{-- Fase 4: APTO/NO APTO --}}
                        <div class="rounded-lg p-6
                            @if($resultData['result'] === 'APTO') bg-green-50 border border-green-200
                            @else bg-red-50 border border-red-200
                            @endif">
                            <h3 class="text-3xl font-bold mb-3
                                @if($resultData['result'] === 'APTO') text-green-800
                                @else text-red-800
                                @endif">
                                @if($resultData['result'] === 'APTO')
                                    <i class="fas fa-check-circle"></i> APTO
                                @else
                                    <i class="fas fa-times-circle"></i> NO APTO
                                @endif
                            </h3>
                            <p class="
                                @if($resultData['result'] === 'APTO') text-green-700
                                @else text-red-700
                                @endif">
                                @if($resultData['result'] === 'APTO')
                                    <strong>¡Felicidades!</strong> Has cumplido con los requisitos mínimos de la convocatoria.
                                @else
                                    No cumples con uno o más requisitos mínimos de la convocatoria.
                                @endif
                            </p>
                            @if($resultData['reason'])
                                <hr class="my-4
                                    @if($resultData['result'] === 'APTO') border-green-200
                                    @else border-red-200
                                    @endif">
                                <p class="
                                    @if($resultData['result'] === 'APTO') text-green-700
                                    @else text-red-700
                                    @endif">
                                    <strong>Motivo:</strong><br>
                                    {{ $resultData['reason'] }}
                                </p>
                            @endif
                        </div>

                    @elseif($publication->phase->value === 'PHASE_07')
                        {{-- Fase 7: Ranking Curricular --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                <h6 class="text-sm text-gray-500 mb-2">Tu Ranking</h6>
                                <h2 class="text-4xl font-bold text-blue-600">
                                    <i class="fas fa-trophy"></i> {{ $resultData['ranking'] }}
                                </h2>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                <h6 class="text-sm text-gray-500 mb-2">Puntaje Curricular</h6>
                                <h2 class="text-4xl font-bold text-green-600">
                                    {{ number_format($resultData['curriculum_score'], 2) }}
                                </h2>
                                <small class="text-gray-500">de {{ $resultData['max_score'] }} puntos</small>
                            </div>
                        </div>

                    @elseif($publication->phase->value === 'PHASE_09')
                        {{-- Fase 9: Resultados Finales --}}
                        @if($resultData['is_winner'])
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-4">
                                <h4 class="text-xl font-bold text-green-800"><i class="fas fa-trophy"></i> ¡FELICIDADES!</h4>
                                <p class="text-green-700">Has obtenido el <strong>PRIMER LUGAR</strong> en esta convocatoria.</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <h6 class="text-xs text-gray-500 mb-2">Ranking Final</h6>
                                <h3 class="text-2xl font-bold {{ $resultData['is_winner'] ? 'text-yellow-600' : 'text-blue-600' }}">
                                    <i class="fas fa-trophy"></i> {{ $resultData['ranking'] }}
                                </h3>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <h6 class="text-xs text-gray-500 mb-2">P. Curricular</h6>
                                <h4 class="text-xl font-bold text-gray-900">{{ number_format($resultData['curriculum_score'], 2) }}</h4>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <h6 class="text-xs text-gray-500 mb-2">P. Entrevista</h6>
                                <h4 class="text-xl font-bold text-gray-900">{{ number_format($resultData['interview_score'], 2) }}</h4>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <h6 class="text-xs text-gray-500 mb-2">Bonificación</h6>
                                <h4 class="text-xl font-bold text-gray-900">{{ number_format($resultData['bonus'], 2) }}</h4>
                            </div>
                        </div>

                        <div class="bg-blue-600 text-white rounded-lg p-6 text-center">
                            <h6 class="text-sm mb-2">Puntaje Final</h6>
                            <h2 class="text-4xl font-bold">{{ number_format($resultData['final_score'], 2) }}</h2>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h6 class="font-semibold text-gray-900 mb-4">Información</h6>
                        <div class="mb-4">
                            <small class="text-gray-500">Publicado el</small>
                            <div class="text-gray-900">{{ $publication->published_at->format('d/m/Y') }}</div>
                            <div class="text-gray-500 text-sm">{{ $publication->published_at->format('H:i') }} hrs</div>
                        </div>
                        @if($publication->document && $publication->document->signed_pdf_path)
                            <hr class="my-4">
                            <a href="{{ route('applicant.results.download-pdf', $publication) }}"
                               class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-file-pdf mr-2"></i> Descargar Acta Oficial
                            </a>
                            <small class="text-gray-500 text-xs block mt-2 text-center">
                                <i class="fas fa-certificate"></i> Documento con firmas digitales
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Información adicional según fase --}}
    @if($publication->phase->value === 'PHASE_04' && $resultData['result'] === 'APTO')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h5 class="text-lg font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle"></i> Próximos Pasos</h5>
            <p class="text-blue-700">
                Has sido declarado APTO para continuar en el proceso. Estarás habilitado para las siguientes fases
                de evaluación. Te mantendremos informado sobre las siguientes etapas del proceso.
            </p>
        </div>
    @elseif($publication->phase->value === 'PHASE_04' && $resultData['result'] === 'NO APTO')
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h5 class="text-lg font-semibold text-yellow-800 mb-2"><i class="fas fa-exclamation-triangle"></i> Información Importante</h5>
            <p class="text-yellow-700">
                Lamentablemente no cumples con los requisitos mínimos para continuar en esta convocatoria.
                Puedes postular a otras convocatorias disponibles en nuestro portal.
            </p>
        </div>
    @elseif($publication->phase->value === 'PHASE_09')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h5 class="text-lg font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle"></i> Fin del Proceso</h5>
            <p class="text-blue-700">
                Estos son los resultados finales del proceso de selección. Agradecemos tu participación.
                @if($resultData['is_winner'])
                    Un representante de la institución se contactará contigo próximamente con las instrucciones
                    para la contratación.
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
