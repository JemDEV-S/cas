@extends('layouts.app')

@section('title', 'Detalles de Evaluación Automática')

@php
use Modules\Core\ValueObjects\ExperienceDuration;

// Definir helpers solo si no existen (evitar redefinición)
if (!function_exists('formatExperienceValueForEval')) {
    /**
     * Formatea un valor de experiencia (decimal o string) a texto legible
     * Ejemplo: 2.5 -> "2 años y 6 meses"
     */
    function formatExperienceValueForEval($value) {
        if ($value === null) {
            return '-';
        }

        // Si ya es string y no es numérico, devolver tal cual
        if (is_string($value) && !is_numeric($value)) {
            return $value;
        }

        // Si es numérico, formatear usando ExperienceDuration
        if (is_numeric($value)) {
            return ExperienceDuration::fromDecimal((float) $value)->toHuman();
        }

        return (string) $value;
    }
}

if (!function_exists('isExperienceFieldForEval')) {
    /**
     * Detecta si un valor parece ser de experiencia (contiene años o meses)
     */
    function isExperienceFieldForEval($key) {
        // Códigos de criterios de experiencia
        $experienceCriteria = [
            'ELIGIBILITY_GENERAL_EXPERIENCE',
            'ELIGIBILITY_SPECIFIC_EXPERIENCE',
        ];

        // Verificar por código de criterio
        foreach ($experienceCriteria as $criterion) {
            if (str_contains($key, $criterion)) {
                return true;
            }
        }

        // Verificar por palabras clave
        $experienceKeys = ['years', 'months', 'experience', 'experiencia', 'años', 'meses', 'general_experience', 'specific_experience'];
        $keyLower = strtolower($key);
        foreach ($experienceKeys as $expKey) {
            if (str_contains($keyLower, $expKey)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('formatMetadataValueForEval')) {
    /**
     * Formatea el valor de metadata según su tipo
     */
    function formatMetadataValueForEval($key, $value) {
        if ($value === null) {
            return '-';
        }

        // Si es array, formatear cada elemento
        if (is_array($value)) {
            $formatted = [];
            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    $formatted[] = is_numeric($v) && isExperienceFieldForEval($key)
                        ? formatExperienceValueForEval($v)
                        : (string) $v;
                } else {
                    $formattedV = is_numeric($v) && isExperienceFieldForEval($k)
                        ? formatExperienceValueForEval($v)
                        : (string) $v;
                    $formatted[] = "{$k}: {$formattedV}";
                }
            }
            return implode(', ', $formatted);
        }

        // Si es numérico y parece ser experiencia, formatear
        if (is_numeric($value) && isExperienceFieldForEval($key)) {
            return formatExperienceValueForEval($value);
        }

        return (string) $value;
    }
}
@endphp

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Breadcrumb --}}
    <nav class="mb-6" aria-label="breadcrumb">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ route('evaluation.automatic.index') }}" class="hover:text-blue-600">
                    Evaluaciones Automáticas
                </a>
            </li>
            @if($application->jobProfile?->jobPosting)
                <li class="before:content-['/'] before:mx-2">
                    <a href="{{ route('evaluation.automatic.show', $application->jobProfile->jobPosting->id) }}" class="hover:text-blue-600">
                        {{ $application->jobProfile->jobPosting->code }}
                    </a>
                </li>
            @endif
            <li class="before:content-['/'] before:mx-2">
                <span class="text-gray-800 font-medium">{{ $application->code }}</span>
            </li>
        </ol>
    </nav>

    {{-- Encabezado --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">
                Detalles de Evaluación Automática
            </h2>
            <p class="text-gray-600 text-sm">
                <code class="bg-gray-100 px-2 py-1 rounded">{{ $application->code }}</code>
                <span class="mx-2">-</span>
                {{ $application->full_name }}
            </p>
        </div>
        <div>
            @if($application->is_eligible)
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-lg font-semibold rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    APTO
                </span>
            @else
                <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 text-lg font-semibold rounded-lg">
                    <i class="fas fa-times-circle mr-2"></i>
                    NO APTO
                </span>
            @endif
        </div>
    </div>

    {{-- Información del Postulante --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-user mr-2"></i>
                Información del Postulante
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-gray-500 text-sm">Nombre Completo</span>
                    <p class="font-medium text-gray-900">{{ $application->full_name }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">DNI</span>
                    <p class="font-medium text-gray-900">{{ $application->dni }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Perfil Postulado</span>
                    <p class="font-medium text-gray-900">{{ $application->jobProfile?->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Estado de Postulación</span>
                    <p>
                        @switch($application->status->value ?? $application->status)
                            @case('submitted')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Presentada
                                </span>
                                @break
                            @case('eligible')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Elegible
                                </span>
                                @break
                            @case('not_eligible')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    No Elegible
                                </span>
                                @break
                            @default
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $application->status->value ?? $application->status }}
                                </span>
                        @endswitch
                    </p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Fecha de Evaluación</span>
                    <p class="font-medium text-gray-900">
                        {{ $application->eligibility_checked_at?->format('d/m/Y H:i:s') ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">Evaluado por</span>
                    <p class="font-medium text-gray-900">
                        {{ $application->eligibilityChecker?->name ?? 'Sistema Automático' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($evaluation)
        {{-- Resumen de Evaluación --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Resumen de Evaluación
                </h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center border border-blue-100">
                        <i class="fas fa-list-check text-2xl text-blue-500 mb-2"></i>
                        <h3 class="text-2xl font-bold text-blue-700">{{ $evaluation->details->count() }}</h3>
                        <p class="text-blue-600 text-xs">Criterios Evaluados</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                        <i class="fas fa-check text-2xl text-green-500 mb-2"></i>
                        <h3 class="text-2xl font-bold text-green-700">{{ $evaluation->details->where('score', '>=', 1)->count() }}</h3>
                        <p class="text-green-600 text-xs">Cumple</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center border border-red-100">
                        <i class="fas fa-times text-2xl text-red-500 mb-2"></i>
                        <h3 class="text-2xl font-bold text-red-700">{{ $evaluation->details->where('score', '<', 1)->count() }}</h3>
                        <p class="text-red-600 text-xs">No Cumple</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <i class="fas fa-clock text-2xl text-gray-500 mb-2"></i>
                        <p class="text-sm font-medium text-gray-700">
                            {{ $evaluation->submitted_at?->format('d/m/Y') ?? 'N/A' }}
                        </p>
                        <p class="text-gray-600 text-xs">Fecha Evaluación</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detalle de Criterios --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Detalle de Criterios Evaluados
                </h5>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criterio</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($evaluation->details->sortBy('criterion.order') as $detail)
                                @php
                                    $isNotApplicable = $detail->metadata['not_applicable'] ?? false;
                                    $passed = $detail->score >= 1;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $detail->criterion?->name ?? 'Criterio Desconocido' }}
                                        </div>
                                        @if($detail->criterion?->description)
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ Str::limit($detail->criterion->description, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($isNotApplicable)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                                <i class="fas fa-minus-circle mr-1"></i>
                                                No Aplica
                                            </span>
                                        @elseif($passed)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Cumple
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                No Cumple
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $detail->comments ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($detail->metadata && !$isNotApplicable)
                                            @php
                                                $meta = $detail->metadata;
                                                $required = $meta['required'] ?? null;
                                                $achieved = $meta['achieved'] ?? null;
                                                $criterionCode = $detail->criterion?->code ?? '';
                                            @endphp
                                            @if($required !== null || $achieved !== null)
                                                <div class="space-y-1">
                                                    @if($required !== null)
                                                        <div class="text-gray-600">
                                                            <span class="font-medium">Requerido:</span>
                                                            {{ formatMetadataValueForEval('required_' . $criterionCode, $required) }}
                                                        </div>
                                                    @endif
                                                    @if($achieved !== null)
                                                        <div class="text-gray-600">
                                                            <span class="font-medium">Obtenido:</span>
                                                            {{ formatMetadataValueForEval('achieved_' . $criterionCode, $achieved) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No hay criterios evaluados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- Sin evaluación --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3 text-xl"></i>
                <div>
                    <p class="text-yellow-800 font-medium">Sin datos de evaluación</p>
                    <p class="text-yellow-700 text-sm mt-1">
                        No se encontró información de evaluación automática de Fase 4 para esta postulación.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Motivos de No Elegibilidad --}}
    @if(!$application->is_eligible && $application->ineligibility_reason)
        <div class="bg-red-50 border border-red-200 rounded-lg mb-6">
            <div class="bg-red-100 px-6 py-4 border-b border-red-200">
                <h5 class="text-lg font-semibold text-red-800">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Motivos de No Elegibilidad
                </h5>
            </div>
            <div class="p-6">
                <ul class="space-y-2">
                    @foreach(explode("\n", $application->ineligibility_reason) as $reason)
                        @if(trim($reason))
                            <li class="flex items-start text-red-700">
                                <i class="fas fa-times text-red-500 mt-1 mr-2"></i>
                                <span>{{ trim($reason) }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Botón Volver --}}
    <div class="flex justify-start">
        @if($application->jobProfile?->jobPosting)
            <a href="{{ route('evaluation.automatic.show', $application->jobProfile->jobPosting->id) }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Detalles de Convocatoria
            </a>
        @else
            <a href="{{ route('evaluation.automatic.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Evaluaciones Automáticas
            </a>
        @endif
    </div>
</div>
@endsection
