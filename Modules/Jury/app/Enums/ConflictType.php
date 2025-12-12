<?php

namespace Modules\Jury\Enums;

enum ConflictType: string
{
    case FAMILY = 'FAMILY';
    case LABOR = 'LABOR';
    case PROFESSIONAL = 'PROFESSIONAL';
    case FINANCIAL = 'FINANCIAL';
    case PERSONAL = 'PERSONAL';
    case ACADEMIC = 'ACADEMIC';
    case PRIOR_EVALUATION = 'PRIOR_EVALUATION';
    case OTHER = 'OTHER';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::FAMILY => 'Familiar',
            self::LABOR => 'Laboral',
            self::PROFESSIONAL => 'Profesional',
            self::FINANCIAL => 'Financiero',
            self::PERSONAL => 'Personal',
            self::ACADEMIC => 'Académico',
            self::PRIOR_EVALUATION => 'Evaluación Previa',
            self::OTHER => 'Otro',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::FAMILY => 'Relación familiar directa',
            self::LABOR => 'Relación laboral actual o reciente',
            self::PROFESSIONAL => 'Relación profesional',
            self::FINANCIAL => 'Interés financiero directo',
            self::PERSONAL => 'Amistad cercana o enemistad',
            self::ACADEMIC => 'Relación académica (asesor/asesorado)',
            self::PRIOR_EVALUATION => 'Evaluó previamente al postulante',
            self::OTHER => 'Otro tipo de conflicto',
        };
    }

    /**
     * Get recommended severity
     */
    public function recommendedSeverity(): ConflictSeverity
    {
        return match($this) {
            self::FAMILY => ConflictSeverity::CRITICAL,
            self::LABOR => ConflictSeverity::HIGH,
            self::FINANCIAL => ConflictSeverity::CRITICAL,
            self::PROFESSIONAL => ConflictSeverity::MEDIUM,
            self::PERSONAL => ConflictSeverity::HIGH,
            self::ACADEMIC => ConflictSeverity::HIGH,
            self::PRIOR_EVALUATION => ConflictSeverity::MEDIUM,
            self::OTHER => ConflictSeverity::MEDIUM,
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::FAMILY => 'fa-users',
            self::LABOR => 'fa-briefcase',
            self::PROFESSIONAL => 'fa-handshake',
            self::FINANCIAL => 'fa-dollar-sign',
            self::PERSONAL => 'fa-heart',
            self::ACADEMIC => 'fa-graduation-cap',
            self::PRIOR_EVALUATION => 'fa-clipboard-check',
            self::OTHER => 'fa-exclamation-triangle',
        };
    }

    /**
     * Get all values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all options for select
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}