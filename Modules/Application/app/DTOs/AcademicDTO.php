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
        public readonly ?string $careerId = null, // 💎 ID de la carrera del catálogo
        public readonly bool $isRelatedCareer = false, // 💎 NUEVO: Es carrera afín
        public readonly ?string $relatedCareerName = null, // 💎 NUEVO: Nombre de carrera afín
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            institutionName: $data['institution_name'],
            degreeType: $data['degree_type'],
            degreeTitle: $data['degree_title'],
            issueDate: $data['issue_date'],
            careerField: $data['career_field'] ?? null,
            careerId: $data['career_id'] ?? null,
            isRelatedCareer: $data['is_related_career'] ?? false,
            relatedCareerName: $data['related_career_name'] ?? null,
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
            'career_id' => $this->careerId,
            'is_related_career' => $this->isRelatedCareer,
            'related_career_name' => $this->relatedCareerName,
        ];
    }
}
