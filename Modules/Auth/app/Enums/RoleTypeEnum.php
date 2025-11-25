<?php

namespace Modules\Auth\Enums;

enum RoleTypeEnum: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN_RRHH = 'ADMIN_RRHH';
    case AREA_USER = 'AREA_USER';
    case RRHH_REVIEWER = 'RRHH_REVIEWER';
    case JURY = 'JURY';
    case APPLICANT = 'APPLICANT';
    case VIEWER = 'VIEWER';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrador',
            self::ADMIN_RRHH => 'Administrador RRHH',
            self::AREA_USER => 'Usuario de Área',
            self::RRHH_REVIEWER => 'Revisor RRHH',
            self::JURY => 'Jurado',
            self::APPLICANT => 'Postulante',
            self::VIEWER => 'Visualizador',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Control total del sistema',
            self::ADMIN_RRHH => 'Gestión completa de convocatorias y usuarios',
            self::AREA_USER => 'Solicita perfiles de puesto para su área',
            self::RRHH_REVIEWER => 'Revisa y aprueba perfiles de puesto',
            self::JURY => 'Evalúa postulaciones en el proceso',
            self::APPLICANT => 'Postula a convocatorias públicas',
            self::VIEWER => 'Solo visualización sin modificaciones',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::SUPER_ADMIN => ['*'],
            self::ADMIN_RRHH => [
                'jobposting.*',
                'jobprofile.*',
                'user.view',
                'user.create',
                'organization.*',
                'reporting.*',
            ],
            self::AREA_USER => [
                'jobprofile.create',
                'jobprofile.view',
                'jobprofile.update.own',
            ],
            self::RRHH_REVIEWER => [
                'jobprofile.view',
                'jobprofile.review',
                'jobprofile.approve',
            ],
            self::JURY => [
                'evaluation.*',
                'application.view',
            ],
            self::APPLICANT => [
                'application.create',
                'application.view.own',
                'jobposting.view.public',
            ],
            self::VIEWER => [
                '*.view',
            ],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ],
            self::cases()
        );
    }
}
