<?php

namespace Modules\Auth\DTOs;

/**
 * DTO inmutable que representa los datos de una persona desde RENIEC
 * Los datos se mantienen en MAYÚSCULAS tal como vienen de la API
 */
class ReniecPersonDataDTO
{
    public function __construct(
        public readonly string $dni,
        public readonly string $nombres,
        public readonly string $apellidoPaterno,
        public readonly string $apellidoMaterno,
        public readonly string $nombreCompleto,
        public readonly string $genero,
        public readonly string $fechaNacimiento,
        public readonly string $codigoVerificacion,
    ) {}

    /**
     * Crear desde respuesta de la API de PeruDevs
     */
    public static function fromApiResponse(array $resultado): self
    {
        return new self(
            dni: $resultado['id'] ?? '',
            nombres: $resultado['nombres'] ?? '',
            apellidoPaterno: $resultado['apellido_paterno'] ?? '',
            apellidoMaterno: $resultado['apellido_materno'] ?? '',
            nombreCompleto: $resultado['nombre_completo'] ?? '',
            genero: $resultado['genero'] ?? '',
            fechaNacimiento: $resultado['fecha_nacimiento'] ?? '',
            codigoVerificacion: $resultado['codigo_verificacion'] ?? '',
        );
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'nombre_completo' => $this->nombreCompleto,
            'genero' => $this->genero,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'codigo_verificacion' => $this->codigoVerificacion,
        ];
    }

    /**
     * Obtener datos para registro de usuario
     * Formato compatible con el sistema actual
     */
    public function toRegistrationData(): array
    {
        return [
            'first_name' => $this->nombres,
            'last_name' => trim($this->apellidoPaterno . ' ' . $this->apellidoMaterno),
            'full_name' => $this->nombreCompleto,
            'gender' => $this->convertGender($this->genero),
            'birth_date' => $this->convertDateFormat($this->fechaNacimiento),
        ];
    }

    /**
     * Convertir género de formato API (M/F) a formato del sistema (MASCULINO/FEMENINO)
     */
    private function convertGender(string $gender): string
    {
        return match(strtoupper($gender)) {
            'M' => 'MASCULINO',
            'F' => 'FEMENINO',
            default => $gender, // Si ya viene en formato completo, devolverlo tal cual
        };
    }

    /**
     * Convertir fecha de formato DD/MM/YYYY a YYYY-MM-DD
     */
    private function convertDateFormat(string $date): string
    {
        // Si la fecha ya está en formato YYYY-MM-DD, devolverla tal cual
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Convertir de DD/MM/YYYY a YYYY-MM-DD
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        return $date;
    }

    /**
     * Verificar si el código verificador coincide con el proporcionado
     */
    public function hasCheckDigit(string $checkDigit): bool
    {
        return strtoupper($this->codigoVerificacion) === strtoupper($checkDigit);
    }
}
