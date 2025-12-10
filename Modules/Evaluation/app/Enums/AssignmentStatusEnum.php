<?php

namespace Modules\Evaluation\Enums;

enum AssignmentStatusEnum: string
{
    case PENDING = 'PENDING';           // Pendiente
    case IN_PROGRESS = 'IN_PROGRESS';   // En progreso
    case COMPLETED = 'COMPLETED';       // Completada
    case CANCELLED = 'CANCELLED';       // Cancelada
    case REASSIGNED = 'REASSIGNED';     // Reasignada

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::COMPLETED => 'Completada',
            self::CANCELLED => 'Cancelada',
            self::REASSIGNED => 'Reasignada',
        };
    }

    /**
     * Obtener la clase de badge para UI
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'badge-warning',
            self::IN_PROGRESS => 'badge-info',
            self::COMPLETED => 'badge-success',
            self::CANCELLED => 'badge-danger',
            self::REASSIGNED => 'badge-secondary',
        };
    }

    /**
     * Verificar si está activa
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::IN_PROGRESS,
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