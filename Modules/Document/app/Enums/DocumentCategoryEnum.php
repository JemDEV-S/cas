<?php

namespace Modules\Document\Enums;

enum DocumentCategoryEnum: string
{
    case PERFIL = 'perfil';
    case CONVOCATORIA = 'convocatoria';
    case CONVOCATORIA_COMPLETA = 'convocatoria_completa';
    case EVALUACION = 'evaluacion';
    case CONTRATO = 'contrato';
    case ACTA = 'acta';
    case CERTIFICADO = 'certificado';
    case CONSTANCIA = 'constancia';
    case RESOLUCION = 'resolucion';
    case MEMORANDUM = 'memorandum';
    case OFICIO = 'oficio';
    case INFORME = 'informe';
    case OTRO = 'otro';

    public function label(): string
    {
        return match($this) {
            self::PERFIL => 'Perfil de Puesto',
            self::CONVOCATORIA => 'Convocatoria',
            self::CONVOCATORIA_COMPLETA => 'Convocatoria Completa',
            self::EVALUACION => 'Evaluación',
            self::CONTRATO => 'Contrato',
            self::ACTA => 'Acta',
            self::CERTIFICADO => 'Certificado',
            self::CONSTANCIA => 'Constancia',
            self::RESOLUCION => 'Resolución',
            self::MEMORANDUM => 'Memorándum',
            self::OFICIO => 'Oficio',
            self::INFORME => 'Informe',
            self::OTRO => 'Otro',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PERFIL => 'fas fa-user-tie',
            self::CONVOCATORIA => 'fas fa-bullhorn',
            self::CONVOCATORIA_COMPLETA => 'fas fa-file-pdf',
            self::EVALUACION => 'fas fa-clipboard-check',
            self::CONTRATO => 'fas fa-file-contract',
            self::ACTA => 'fas fa-file-alt',
            self::CERTIFICADO => 'fas fa-certificate',
            self::CONSTANCIA => 'fas fa-award',
            self::RESOLUCION => 'fas fa-gavel',
            self::MEMORANDUM => 'fas fa-file-signature',
            self::OFICIO => 'fas fa-envelope',
            self::INFORME => 'fas fa-file-invoice',
            self::OTRO => 'fas fa-file',
        };
    }
}
