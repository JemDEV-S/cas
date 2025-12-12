<?php

namespace Modules\Jury\Enums;

enum MemberType: string
{
    case TITULAR = 'TITULAR';
    case SUPLENTE = 'SUPLENTE';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::TITULAR => 'Titular',
            self::SUPLENTE => 'Suplente',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::TITULAR => 'Miembro titular del jurado',
            self::SUPLENTE => 'Miembro suplente del jurado',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::TITULAR => 'primary',
            self::SUPLENTE => 'secondary',
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