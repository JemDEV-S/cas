<?php

namespace Modules\Core\DTOs;

/**
 * Sort DTO
 *
 * Data Transfer Object para ordenamiento.
 */
class SortDTO
{
    public ?string $column;
    public string $direction;

    public function __construct(?string $column = null, string $direction = 'asc')
    {
        $this->column = $column;
        $this->direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';
    }

    public function hasSort(): bool
    {
        return !is_null($this->column);
    }

    public function isAscending(): bool
    {
        return $this->direction === 'asc';
    }

    public function isDescending(): bool
    {
        return $this->direction === 'desc';
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['sort_by'] ?? $data['column'] ?? null,
            $data['sort_direction'] ?? $data['direction'] ?? 'asc'
        );
    }

    public function toArray(): array
    {
        return [
            'column' => $this->column,
            'direction' => $this->direction,
        ];
    }
}
