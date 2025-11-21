<?php

namespace Modules\Core\DTOs;

/**
 * Pagination DTO
 *
 * Data Transfer Object para paginación.
 */
class PaginationDTO
{
    public int $page;
    public int $perPage;
    public ?string $search;
    public ?string $sortBy;
    public string $sortDirection;

    public function __construct(
        int $page = 1,
        int $perPage = 15,
        ?string $search = null,
        ?string $sortBy = null,
        string $sortDirection = 'asc'
    ) {
        $this->page = max(1, $page);
        $this->perPage = min(100, max(1, $perPage));
        $this->search = $search;
        $this->sortBy = $sortBy;
        $this->sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'asc';
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['page'] ?? 1,
            $data['per_page'] ?? 15,
            $data['search'] ?? null,
            $data['sort_by'] ?? null,
            $data['sort_direction'] ?? 'asc'
        );
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'search' => $this->search,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }
}
