<?php

namespace Modules\Application\DTOs;

class AcademicDTO
{
    public function __construct(
        public readonly string $institutionName,
        public readonly string $degreeType,
        public readonly string $degreeTitle,
        public readonly string $issueDate,
        public readonly ?string $careerField = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            institutionName: $data['institution_name'],
            degreeType: $data['degree_type'],
            degreeTitle: $data['degree_title'],
            issueDate: $data['issue_date'],
            careerField: $data['career_field'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'institution_name' => $this->institutionName,
            'degree_type' => $this->degreeType,
            'degree_title' => $this->degreeTitle,
            'issue_date' => $this->issueDate,
            'career_field' => $this->careerField,
        ];
    }
}
