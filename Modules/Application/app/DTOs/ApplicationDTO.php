<?php

namespace Modules\Application\DTOs;

class ApplicationDTO
{
    public function __construct(
        public readonly string $jobProfileVacancyId,
        public readonly string $applicantId,
        public readonly PersonalDataDTO $personalData,
        public readonly array $academics,
        public readonly array $experiences,
        public readonly array $trainings,
        public readonly array $specialConditions,
        public readonly array $professionalRegistrations,
        public readonly array $knowledge,
        public readonly bool $termsAccepted,
        public readonly ?string $ipAddress = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            jobProfileVacancyId: $data['job_profile_vacancy_id'],
            applicantId: $data['applicant_id'],
            personalData: PersonalDataDTO::fromArray($data['personal_data']),
            academics: array_map(
                fn($item) => AcademicDTO::fromArray($item),
                $data['academics'] ?? []
            ),
            experiences: array_map(
                fn($item) => ExperienceDTO::fromArray($item),
                $data['experiences'] ?? []
            ),
            trainings: array_map(
                fn($item) => TrainingDTO::fromArray($item),
                $data['trainings'] ?? []
            ),
            specialConditions: array_map(
                fn($item) => SpecialConditionDTO::fromArray($item),
                $data['special_conditions'] ?? []
            ),
            professionalRegistrations: array_map(
                fn($item) => ProfessionalRegistrationDTO::fromArray($item),
                $data['professional_registrations'] ?? []
            ),
            knowledge: array_map(
                fn($item) => KnowledgeDTO::fromArray($item),
                $data['knowledge'] ?? []
            ),
            termsAccepted: $data['terms_accepted'],
            ipAddress: $data['ip_address'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'job_profile_vacancy_id' => $this->jobProfileVacancyId,
            'applicant_id' => $this->applicantId,
            'personal_data' => $this->personalData->toArray(),
            'academics' => array_map(fn($item) => $item->toArray(), $this->academics),
            'experiences' => array_map(fn($item) => $item->toArray(), $this->experiences),
            'trainings' => array_map(fn($item) => $item->toArray(), $this->trainings),
            'special_conditions' => array_map(fn($item) => $item->toArray(), $this->specialConditions),
            'professional_registrations' => array_map(fn($item) => $item->toArray(), $this->professionalRegistrations),
            'knowledge' => array_map(fn($item) => $item->toArray(), $this->knowledge),
            'terms_accepted' => $this->termsAccepted,
            'ip_address' => $this->ipAddress,
            'notes' => $this->notes,
        ];
    }
}
