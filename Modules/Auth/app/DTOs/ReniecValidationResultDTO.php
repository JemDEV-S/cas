<?php

namespace Modules\Auth\DTOs;

/**
 * DTO que representa el resultado de una validación de DNI con RENIEC
 */
class ReniecValidationResultDTO
{
    public function __construct(
        public readonly bool $isValid,
        public readonly string $message,
        public readonly ?ReniecPersonDataDTO $personData = null,
    ) {}

    /**
     * Crear resultado exitoso
     */
    public static function success(ReniecPersonDataDTO $personData, string $message = 'DNI validado correctamente'): self
    {
        return new self(
            isValid: true,
            message: $message,
            personData: $personData,
        );
    }

    /**
     * Crear resultado fallido
     */
    public static function failure(string $message): self
    {
        return new self(
            isValid: false,
            message: $message,
            personData: null,
        );
    }

    /**
     * Convertir a array para respuesta JSON
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid,
            'message' => $this->message,
            'data' => $this->personData?->toRegistrationData(),
        ];
    }

    /**
     * Convertir a respuesta completa con todos los datos
     */
    public function toFullArray(): array
    {
        return [
            'valid' => $this->isValid,
            'message' => $this->message,
            'data' => $this->personData?->toArray(),
        ];
    }
}
