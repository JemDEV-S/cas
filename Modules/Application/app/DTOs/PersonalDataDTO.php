<?php

namespace Modules\Application\DTOs;

class PersonalDataDTO
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $dni,
        public readonly string $birthDate,
        public readonly string $address,
        public readonly string $mobilePhone,
        public readonly string $email,
        public readonly ?string $phone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fullName: $data['full_name'],
            dni: $data['dni'],
            birthDate: $data['birth_date'],
            address: $data['address'],
            mobilePhone: $data['mobile_phone'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'dni' => $this->dni,
            'birth_date' => $this->birthDate,
            'address' => $this->address,
            'mobile_phone' => $this->mobilePhone,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
