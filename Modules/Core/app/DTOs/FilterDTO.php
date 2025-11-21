<?php

namespace Modules\Core\DTOs;

/**
 * Filter DTO
 *
 * Data Transfer Object para filtros.
 */
class FilterDTO
{
    public array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $this->sanitizeFilters($filters);
    }

    private function sanitizeFilters(array $filters): array
    {
        return array_filter($filters, function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    public function has(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    public function get(string $key, $default = null)
    {
        return $this->filters[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->filters;
    }

    public function isEmpty(): bool
    {
        return empty($this->filters);
    }

    public static function fromRequest(array $data): self
    {
        return new self($data);
    }

    public function toArray(): array
    {
        return $this->filters;
    }
}
