<?php

namespace Modules\Evaluation\Enums;

enum EvaluationStatusEnum: string
{
    case ASSIGNED = 'ASSIGNED';           // Asignada
    case IN_PROGRESS = 'IN_PROGRESS';     // En progreso (borrador)
    case SUBMITTED = 'SUBMITTED';         // Enviada
    case MODIFIED = 'MODIFIED';           // Modificada (después de envío)
    case CANCELLED = 'CANCELLED';         // Cancelada

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::ASSIGNED => 'Asignada',
            self::IN_PROGRESS => 'En Progreso',
            self::SUBMITTED => 'Enviada',
            self::MODIFIED => 'Modificada',
            self::CANCELLED => 'Cancelada',
        };
    }

    /**
     * Obtener la clase de badge para UI
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::ASSIGNED => 'badge-info',
            self::IN_PROGRESS => 'badge-warning',
            self::SUBMITTED => 'badge-success',
            self::MODIFIED => 'badge-primary',
            self::CANCELLED => 'badge-danger',
        };
    }

    /**
     * Verificar si puede ser editada
     */
    public function canEdit(): bool
    {
        return in_array($this, [
            self::ASSIGNED,
            self::IN_PROGRESS,
        ]);
    }

    /**
     * Verificar si fue completada
     */
    public function isCompleted(): bool
    {
        return in_array($this, [
            self::SUBMITTED,
            self::MODIFIED,
        ]);
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener opciones para select
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}