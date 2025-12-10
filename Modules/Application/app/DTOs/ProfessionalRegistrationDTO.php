<?php

namespace Modules\Application\DTOs;

class ProfessionalRegistrationDTO
{
    public function __construct(
        public readonly string $registrationType,
        public readonly ?string $issuingEntity = null,
        public readonly ?string $registrationNumber = null,
        public readonly ?string $issueDate = null,
        public readonly ?string $expiryDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            registrationType: $data['registration_type'],
            issuingEntity: $data['issuing_entity'] ?? null,
            registrationNumber: $data['registration_number'] ?? null,
            issueDate: $data['issue_date'] ?? null,
            expiryDate: $data['expiry_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'registration_type' => $this->registrationType,
            'issuing_entity' => $this->issuingEntity,
            'registration_number' => $this->registrationNumber,
            'issue_date' => $this->issueDate,
            'expiry_date' => $this->expiryDate,
        ];
    }
}
