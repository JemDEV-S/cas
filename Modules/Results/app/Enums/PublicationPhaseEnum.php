<?php

namespace Modules\Results\Enums;

enum PublicationPhaseEnum: string
{
    case PHASE_04 = 'PHASE_04';  // Evaluación de Requisitos Mínimos (Elegibilidad)
    case PHASE_07 = 'PHASE_07';  // Evaluación Curricular
    case PHASE_09 = 'PHASE_09';  // Resultados Finales (Post-Entrevista)

    /**
     * Obtener el nombre legible de la fase
     */
    public function label(): string
    {
        return match($this) {
            self::PHASE_04 => 'Evaluación de Requisitos Mínimos',
            self::PHASE_07 => 'Evaluación Curricular',
            self::PHASE_09 => 'Resultados Finales',
        };
    }

    /**
     * Obtener descripción de la fase
     */
    public function description(): string
    {
        return match($this) {
            self::PHASE_04 => 'Resultados de evaluación de elegibilidad (APTO/NO APTO)',
            self::PHASE_07 => 'Ranking de evaluación curricular con puntajes',
            self::PHASE_09 => 'Ranking final post-entrevista',
        };
    }

    /**
     * Código de template asociado
     */
    public function templateCode(): string
    {
        return match($this) {
            self::PHASE_04 => 'RESULT_ELIGIBILITY',
            self::PHASE_07 => 'RESULT_CURRICULUM',
            self::PHASE_09 => 'RESULT_FINAL',
        };
    }

    /**
     * Título del documento
     */
    public function documentTitle(): string
    {
        return match($this) {
            self::PHASE_04 => 'ACTA DE RESULTADOS - EVALUACIÓN DE REQUISITOS MÍNIMOS',
            self::PHASE_07 => 'ACTA DE RESULTADOS - EVALUACIÓN CURRICULAR',
            self::PHASE_09 => 'ACTA DE RESULTADOS FINALES',
        };
    }
}
