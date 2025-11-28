<?php

namespace Modules\JobProfile\Enums;

enum EducationLevelEnum: string
{
    case SECUNDARIA = 'secundaria';
    case TECNICO = 'tecnico';
    case UNIVERSITARIO = 'universitario';
    case POSTGRADO = 'postgrado';

    public function label(): string
    {
        return match($this) {
            self::SECUNDARIA => 'Educación Secundaria',
            self::TECNICO => 'Técnico',
            self::UNIVERSITARIO => 'Universitario',
            self::POSTGRADO => 'Postgrado',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SECUNDARIA => 'Educación secundaria completa',
            self::TECNICO => 'Título técnico o tecnológico',
            self::UNIVERSITARIO => 'Título universitario o bachiller',
            self::POSTGRADO => 'Maestría, Doctorado o Especialización',
        };
    }

    public function level(): int
    {
        return match($this) {
            self::SECUNDARIA => 1,
            self::TECNICO => 2,
            self::UNIVERSITARIO => 3,
            self::POSTGRADO => 4,
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
            'description' => $case->description(),
            'level' => $case->level(),
        ], self::cases());
    }

    public static function selectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

}
