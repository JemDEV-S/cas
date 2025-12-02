<?php

namespace Modules\JobProfile\Enums;

enum EducationLevelEnum: string
{
    case SECUNDARIA = 'secundaria';
    case ESTUDIOS_TECNICOS = 'estudios_tecnicos';
    case EGRESADO_TECNICO = 'egresado_tecnico';
    case TITULO_TECNICO = 'titulo_tecnico';
    case ESTUDIOS_UNIVERSITARIOS = 'estudios_universitarios';
    case EGRESADO_UNIVERSITARIO = 'egresado_universitario';
    case BACHILLER = 'bachiller';
    case TITULO_PROFESIONAL = 'titulo_profesional';
    case POSTGRADO = 'postgrado';

    public function label(): string
    {
        return match($this) {
            self::SECUNDARIA => 'Educación Secundaria Completa',
            self::ESTUDIOS_TECNICOS => 'Estudios Técnicos',
            self::EGRESADO_TECNICO => 'Egresado de Instituto Técnico/Superior',
            self::TITULO_TECNICO => 'Título Técnico',
            self::ESTUDIOS_UNIVERSITARIOS => 'Estudios Universitarios',
            self::EGRESADO_UNIVERSITARIO => 'Egresado Universitario',
            self::BACHILLER => 'Grado de Bachiller',
            self::TITULO_PROFESIONAL => 'Título Profesional',
            self::POSTGRADO => 'Postgrado (Maestría/Doctorado)',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SECUNDARIA => 'Educación secundaria completa',
            self::ESTUDIOS_TECNICOS => 'Cursando estudios técnicos en instituto',
            self::EGRESADO_TECNICO => 'Egresado de instituto técnico o superior',
            self::TITULO_TECNICO => 'Título técnico o tecnológico',
            self::ESTUDIOS_UNIVERSITARIOS => 'Cursando estudios universitarios',
            self::EGRESADO_UNIVERSITARIO => 'Egresado universitario',
            self::BACHILLER => 'Grado académico de bachiller',
            self::TITULO_PROFESIONAL => 'Título profesional universitario',
            self::POSTGRADO => 'Maestría, Doctorado o Especialización',
        };
    }

    public function level(): int
    {
        return match($this) {
            self::SECUNDARIA => 1,
            self::ESTUDIOS_TECNICOS => 2,
            self::EGRESADO_TECNICO => 3,
            self::TITULO_TECNICO => 4,
            self::ESTUDIOS_UNIVERSITARIOS => 5,
            self::EGRESADO_UNIVERSITARIO => 6,
            self::BACHILLER => 7,
            self::TITULO_PROFESIONAL => 8,
            self::POSTGRADO => 9,
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

    /**
     * Obtiene el grupo educativo (técnico, universitario, postgrado)
     */
    public function group(): string
    {
        return match($this) {
            self::SECUNDARIA => 'secundaria',
            self::ESTUDIOS_TECNICOS,
            self::EGRESADO_TECNICO,
            self::TITULO_TECNICO => 'tecnico',
            self::ESTUDIOS_UNIVERSITARIOS,
            self::EGRESADO_UNIVERSITARIO,
            self::BACHILLER,
            self::TITULO_PROFESIONAL => 'universitario',
            self::POSTGRADO => 'postgrado',
        };
    }

    /**
     * Valida si un nivel educativo cumple con el requisito mínimo
     */
    public function meetsMinimum(EducationLevelEnum $minimum): bool
    {
        return $this->level() >= $minimum->level();
    }

    /**
     * Formatea múltiples niveles educativos como string
     */
    public static function formatMultiple(array $levels): string
    {
        if (empty($levels)) {
            return 'No especificado';
        }

        $labels = array_map(function($level) {
            return is_string($level)
                ? self::from($level)->label()
                : $level->label();
        }, $levels);

        if (count($labels) === 1) {
            return $labels[0];
        }

        if (count($labels) === 2) {
            return implode(' o ', $labels);
        }

        $last = array_pop($labels);
        return implode(', ', $labels) . ' o ' . $last;
    }

}
