<?php

namespace Modules\JobPosting\Enums;

enum ScheduleStatusEnum: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case DELAYED = 'DELAYED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::COMPLETED => 'Completada',
            self::DELAYED => 'Retrasada',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'green',
            self::DELAYED => 'orange',
            self::CANCELLED => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-gradient-to-r from-gray-400 to-gray-500',
            self::IN_PROGRESS => 'bg-gradient-to-r from-blue-500 to-indigo-600',
            self::COMPLETED => 'bg-gradient-to-r from-green-500 to-emerald-600',
            self::DELAYED => 'bg-gradient-to-r from-orange-500 to-red-500',
            self::CANCELLED => 'bg-gradient-to-r from-red-500 to-red-700',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => '⏳',
            self::IN_PROGRESS => '▶️',
            self::COMPLETED => '✅',
            self::DELAYED => '⚠️',
            self::CANCELLED => '❌',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}