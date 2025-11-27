<?php

namespace Modules\JobProfile\Enums;

enum JobProfileStatusEnum: string
{
    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case MODIFICATION_REQUESTED = 'modification_requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::IN_REVIEW => 'En Revisión',
            self::MODIFICATION_REQUESTED => 'Modificación Requerida',
            self::APPROVED => 'Aprobado',
            self::REJECTED => 'Rechazado',
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::IN_REVIEW => 'info',
            self::MODIFICATION_REQUESTED => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::ACTIVE => 'primary',
            self::INACTIVE => 'dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'badge' => $case->badge(),
        ], self::cases());
    }
}
