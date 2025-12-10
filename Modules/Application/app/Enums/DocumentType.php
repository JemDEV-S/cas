<?php

namespace Modules\Application\Enums;

enum DocumentType: string
{
    case APPLICATION_FORM = 'DOC_APPLICATION_FORM';
    case CV = 'DOC_CV';
    case DNI = 'DOC_DNI';
    case DEGREE = 'DOC_DEGREE';
    case CERTIFICATE = 'DOC_CERTIFICATE';
    case EXPERIENCE = 'DOC_EXPERIENCE';
    case SPECIAL_CONDITION = 'DOC_SPECIAL_CONDITION';

    public function label(): string
    {
        return match($this) {
            self::APPLICATION_FORM => 'Ficha de Postulación',
            self::CV => 'Curriculum Vitae',
            self::DNI => 'DNI',
            self::DEGREE => 'Título/Grado Académico',
            self::CERTIFICATE => 'Certificado',
            self::EXPERIENCE => 'Constancia de Experiencia',
            self::SPECIAL_CONDITION => 'Documento Condición Especial',
        };
    }

    public function isRequired(): bool
    {
        return in_array($this, [
            self::APPLICATION_FORM,
            self::DNI,
        ]);
    }

    public function requiresSignature(): bool
    {
        return $this === self::APPLICATION_FORM;
    }

    public function allowedMimeTypes(): array
    {
        return match($this) {
            self::APPLICATION_FORM => ['application/pdf'],
            self::CV => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            default => ['application/pdf', 'image/jpeg', 'image/png'],
        };
    }

    public function maxSizeMB(): int
    {
        return match($this) {
            self::APPLICATION_FORM => 10,
            self::CV => 5,
            default => 3,
        };
    }
}
