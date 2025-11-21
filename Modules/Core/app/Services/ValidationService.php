<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validation Service
 *
 * Servicio para validaciones reutilizables.
 */
class ValidationService
{
    /**
     * Valida datos con reglas personalizadas.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return array
     * @throws ValidationException
     */
    public function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Valida un DNI peruano.
     *
     * @param string $dni
     * @return bool
     */
    public function validateDNI(string $dni): bool
    {
        return preg_match('/^\d{8}$/', $dni);
    }

    /**
     * Valida un RUC peruano.
     *
     * @param string $ruc
     * @return bool
     */
    public function validateRUC(string $ruc): bool
    {
        return preg_match('/^(10|15|17|20)\d{9}$/', $ruc);
    }

    /**
     * Valida un email.
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida un número de teléfono peruano.
     *
     * @param string $phone
     * @return bool
     */
    public function validatePhone(string $phone): bool
    {
        // Acepta formatos: 999999999, +51999999999, 51999999999
        return preg_match('/^(\+?51)?9\d{8}$/', str_replace([' ', '-'], '', $phone));
    }

    /**
     * Valida una URL.
     *
     * @param string $url
     * @return bool
     */
    public function validateURL(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida una fecha en formato específico.
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Valida que una fecha sea mayor que otra.
     *
     * @param string $dateAfter
     * @param string $dateBefore
     * @return bool
     */
    public function validateDateAfter(string $dateAfter, string $dateBefore): bool
    {
        return strtotime($dateAfter) > strtotime($dateBefore);
    }

    /**
     * Valida que un valor esté dentro de un rango.
     *
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return bool
     */
    public function validateRange($value, $min, $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Valida que un valor sea único en la base de datos.
     *
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param mixed $exceptId
     * @param string $idColumn
     * @return bool
     */
    public function validateUnique(string $table, string $column, $value, $exceptId = null, string $idColumn = 'id'): bool
    {
        $query = \DB::table($table)->where($column, $value);

        if ($exceptId !== null) {
            $query->where($idColumn, '!=', $exceptId);
        }

        return $query->count() === 0;
    }

    /**
     * Valida una contraseña segura.
     *
     * @param string $password
     * @param int $minLength
     * @return bool
     */
    public function validateStrongPassword(string $password, int $minLength = 8): bool
    {
        // Mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{' . $minLength . ',}$/';
        return preg_match($pattern, $password);
    }

    /**
     * Sanitiza una cadena de texto.
     *
     * @param string $string
     * @return string
     */
    public function sanitize(string $string): string
    {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida un array de IDs como UUIDs válidos.
     *
     * @param array $ids
     * @return bool
     */
    public function validateUUIDs(array $ids): bool
    {
        foreach ($ids as $id) {
            if (!$this->validateUUID($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida un UUID.
     *
     * @param string $uuid
     * @return bool
     */
    public function validateUUID(string $uuid): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
}
