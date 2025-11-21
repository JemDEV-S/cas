<?php

namespace Modules\JobProfile\Enums;

enum JobProfileStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING_REVIEW = 'pending_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::PENDING_REVIEW => 'Pendiente de Revisión',
            self::APPROVED => 'Aprobado',
            self::REJECTED => 'Rechazado',
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
        };
    }
}
