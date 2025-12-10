<?php

namespace Modules\Application\DTOs;

class KnowledgeDTO
{
    public function __construct(
        public readonly string $knowledgeName,
        public readonly ?string $proficiencyLevel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            knowledgeName: $data['knowledge_name'],
            proficiencyLevel: $data['proficiency_level'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'knowledge_name' => $this->knowledgeName,
            'proficiency_level' => $this->proficiencyLevel,
        ];
    }
}
