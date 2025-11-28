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
    public function badgeClass(): string
    {
        return match($this) {
            self::BORRADOR => 'bg-gray-100 text-gray-700 border border-gray-300',
            self::PUBLICADA => 'bg-blue-100 text-blue-700 border border-blue-300',
            self::EN_PROCESO => 'bg-amber-100 text-amber-700 border border-amber-300',
            self::FINALIZADA => 'bg-green-100 text-green-700 border border-green-300',
            self::CANCELADA => 'bg-red-100 text-red-700 border border-red-300',
        };
    }

    public function gradientClass(): string
    {
        return match($this) {
            self::BORRADOR => 'from-gray-500 to-gray-600',
            self::PUBLICADA => 'from-blue-500 to-blue-600',
            self::EN_PROCESO => 'from-amber-500 to-amber-600',
            self::FINALIZADA => 'from-green-500 to-green-600',
            self::CANCELADA => 'from-red-500 to-red-600',
        };
    }

    public function iconEmoji(): string
    {
        return match($this) {
            self::BORRADOR => '📝',
            self::PUBLICADA => '📢',
            self::EN_PROCESO => '⚙️',
            self::FINALIZADA => '✅',
            self::CANCELADA => '❌',
        };
    }
}
