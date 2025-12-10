<?php

namespace Modules\Application\DTOs;

class ExperienceDTO
{
    public function __construct(
        public readonly string $organization,
        public readonly string $position,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly bool $isSpecific = false,
        public readonly bool $isPublicSector = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            organization: $data['organization'],
            position: $data['position'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            isSpecific: $data['is_specific'] ?? false,
            isPublicSector: $data['is_public_sector'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'organization' => $this->organization,
            'position' => $this->position,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_specific' => $this->isSpecific,
            'is_public_sector' => $this->isPublicSector,
        ];
    }
}
