<?php

namespace Modules\JobPosting\Enums;

enum JobPostingStatusEnum: string
{
    case BORRADOR = 'BORRADOR';
    case PUBLICADA = 'PUBLICADA';
    case EN_PROCESO = 'EN_PROCESO';
    case FINALIZADA = 'FINALIZADA';
    case CANCELADA = 'CANCELADA';

    /**
     * Obtener label legible
     */
    public function label(): string
    {
        return match($this) {
            self::BORRADOR => 'Borrador',
            self::PUBLICADA => 'Publicada',
            self::EN_PROCESO => 'En Proceso',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
        };
    }

    /**
     * Obtener color para badge
     */
    public function color(): string
    {
        return match($this) {
            self::BORRADOR => 'gray',
            self::PUBLICADA => 'blue',
            self::EN_PROCESO => 'yellow',
            self::FINALIZADA => 'green',
            self::CANCELADA => 'red',
        };
    }

    /**
     * Obtener clase CSS para badge con gradiente
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::BORRADOR => 'bg-gradient-to-r from-gray-400 to-gray-600',
            self::PUBLICADA => 'bg-gradient-to-r from-blue-500 to-blue-700',
            self::EN_PROCESO => 'bg-gradient-to-r from-amber-400 to-orange-500',
            self::FINALIZADA => 'bg-gradient-to-r from-green-500 to-emerald-600',
            self::CANCELADA => 'bg-gradient-to-r from-red-500 to-red-700',
        };
    }

    /**
     * Obtener icono
     */
    public function icon(): string
    {
        return match($this) {
            self::BORRADOR => '📝',
            self::PUBLICADA => '📢',
            self::EN_PROCESO => '⚙️',
            self::FINALIZADA => '✅',
            self::CANCELADA => '❌',
        };
    }

    /**
     * Puede ser editado
     */
    public function canBeEdited(): bool
    {
        return $this === self::BORRADOR;
    }

    /**
     * Puede ser publicado
     */
    public function canBePublished(): bool
    {
        return $this === self::BORRADOR;
    }

    /**
     * Puede ser cancelado
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this, [self::CANCELADA, self::FINALIZADA]);
    }

    /**
     * Obtener todos los valores
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
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}