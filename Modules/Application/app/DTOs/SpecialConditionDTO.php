<?php

namespace Modules\Application\DTOs;

class SpecialConditionDTO
{
    public function __construct(
        public readonly string $conditionType,
        public readonly float $bonusPercentage,
        public readonly ?string $issuingEntity = null,
        public readonly ?string $documentNumber = null,
        public readonly ?string $issueDate = null,
        public readonly ?string $expiryDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            conditionType: $data['condition_type'],
            bonusPercentage: $data['bonus_percentage'],
            issuingEntity: $data['issuing_entity'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            issueDate: $data['issue_date'] ?? null,
            expiryDate: $data['expiry_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'condition_type' => $this->conditionType,
            'bonus_percentage' => $this->bonusPercentage,
            'issuing_entity' => $this->issuingEntity,
            'document_number' => $this->documentNumber,
            'issue_date' => $this->issueDate,
            'expiry_date' => $this->expiryDate,
        ];
    }
}
