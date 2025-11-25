<?php

namespace Modules\Auth\Enums;

enum PermissionTypeEnum: string
{
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case APPROVE = 'approve';
    case REJECT = 'reject';
    case PUBLISH = 'publish';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case MANAGE = 'manage';

    public function label(): string
    {
        return match($this) {
            self::VIEW => 'Ver',
            self::CREATE => 'Crear',
            self::UPDATE => 'Actualizar',
            self::DELETE => 'Eliminar',
            self::APPROVE => 'Aprobar',
            self::REJECT => 'Rechazar',
            self::PUBLISH => 'Publicar',
            self::EXPORT => 'Exportar',
            self::IMPORT => 'Importar',
            self::MANAGE => 'Gestionar',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::VIEW => 'eye',
            self::CREATE => 'plus',
            self::UPDATE => 'edit',
            self::DELETE => 'trash',
            self::APPROVE => 'check',
            self::REJECT => 'x',
            self::PUBLISH => 'upload',
            self::EXPORT => 'download',
            self::IMPORT => 'upload',
            self::MANAGE => 'settings',
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
                'icon' => $case->icon(),
            ],
            self::cases()
        );
    }
}
