<?php

namespace Modules\Core\Enums;

enum YesNoEnum: string
{
    case YES = 'yes';
    case NO = 'no';

    public function label(): string
    {
        return match($this) {
            self::YES => 'Sí',
            self::NO => 'No',
        };
    }

    public function toBool(): bool
    {
        return $this === self::YES;
    }

    public static function fromBool(bool $value): self
    {
        return $value ? self::YES : self::NO;
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
