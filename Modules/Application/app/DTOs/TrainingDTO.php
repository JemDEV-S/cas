<?php

namespace Modules\Application\DTOs;

class TrainingDTO
{
    public function __construct(
        public readonly string $institution,
        public readonly string $courseName,
        public readonly ?int $academicHours = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            institution: $data['institution'],
            courseName: $data['course_name'],
            academicHours: $data['academic_hours'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'institution' => $this->institution,
            'course_name' => $this->courseName,
            'academic_hours' => $this->academicHours,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
