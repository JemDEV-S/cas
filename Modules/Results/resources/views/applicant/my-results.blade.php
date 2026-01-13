@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold mb-1">Mis Resultados</h2>
        <p class="text-gray-500">
            Consulta los resultados de tus postulaciones
        </p>
    </div>

    @if(empty($results))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="text-center py-12">
                <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
                <h5 class="text-lg font-semibold text-gray-500">No hay resultados publicados</h5>
                <p class="text-gray-500">
                    Aún no se han publicado resultados para tus postulaciones.
                    Te notificaremos por correo cuando estén disponibles.
                </p>
            </div>
        </div>
    @else
        @foreach($results as $result)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 bg-blue-600 text-white rounded-t-lg">
                    <h5 class="text-lg font-semibold">
                        <i class="fas fa-briefcase"></i>
                        {{ $result['posting']->code }}
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="text-sm text-gray-500">Mi Postulación</label>
                            <div class="font-semibold text-gray-900">{{ $result['application']->code }}</div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Vacante</label>
                            <div class="text-gray-900">{{ $result['application']->vacancy->code }}</div>
                        </div>
                    </div>

                    @if($result['publications']->isNotEmpty())
                        <h6 class="text-base font-semibold text-gray-900 mb-4">Resultados Publicados</h6>
                        <div class="space-y-3">
                            @foreach($result['publications'] as $publication)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-grow">
                                            <div class="flex items-center mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                    {{ $publication->phase->label() }}
                                                </span>
                                                <span class="text-gray-500 text-sm">
                                                    Publicado el {{ $publication->published_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="font-semibold text-gray-900">{{ $publication->title }}</div>
                                        </div>
                                        <div class="flex gap-2 ml-4">
                                            <a href="{{ route('applicant.results.show', $publication) }}"
                                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                                <i class="fas fa-eye"></i> Ver Resultado
                                            </a>
                                            @if($publication->document && $publication->document->signed_pdf_path)
                                                <a href="{{ route('applicant.results.download-pdf', $publication) }}"
                                                   class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
