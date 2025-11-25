<?php

namespace Modules\Configuration\Services;

use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Enums\ValueTypeEnum;
use Modules\Core\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Validar un valor de configuración
     */
    public function validate(SystemConfig $config, $value): void
    {
        // Validar tipo de valor
        $this->validateType($config, $value);

        // Validar reglas Laravel
        $this->validateRules($config, $value);

        // Validar rango
        $this->validateRange($config, $value);

        // Validar opciones
        $this->validateOptions($config, $value);

        // Validar longitud
        $this->validateLength($config, $value);
    }

    /**
     * Validar el tipo de valor
     */
    protected function validateType(SystemConfig $config, $value): void
    {
        $type = $config->value_type;

        $isValid = match ($type) {
            ValueTypeEnum::STRING => is_string($value) || is_numeric($value),
            ValueTypeEnum::INTEGER => is_int($value) || (is_string($value) && ctype_digit($value)),
            ValueTypeEnum::FLOAT => is_numeric($value),
            ValueTypeEnum::BOOLEAN => is_bool($value) || in_array($value, ['0', '1', 0, 1, true, false], true),
            ValueTypeEnum::JSON => $this->isValidJson($value),
            ValueTypeEnum::DATE => $this->isValidDate($value),
            ValueTypeEnum::DATETIME => $this->isValidDateTime($value),
            ValueTypeEnum::EMAIL => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            ValueTypeEnum::URL => filter_var($value, FILTER_VALIDATE_URL) !== false,
            default => true,
        };

        if (!$isValid) {
            throw new ValidationException(
                "El valor no es válido para el tipo {$type->value}. Se esperaba {$type->label()}"
            );
        }
    }

    /**
     * Validar reglas de Laravel
     */
    protected function validateRules(SystemConfig $config, $value): void
    {
        $rules = $config->validation_rules ?? [];

        if (empty($rules)) {
            return;
        }

        $validator = Validator::make(
            ['value' => $value],
            ['value' => $rules]
        );

        if ($validator->fails()) {
            throw new ValidationException(
                $validator->errors()->first('value')
            );
        }
    }

    /**
     * Validar rango numérico
     */
    protected function validateRange(SystemConfig $config, $value): void
    {
        if (!is_numeric($value)) {
            return;
        }

        $numValue = is_string($value) ? floatval($value) : $value;

        if ($config->min_value !== null && $numValue < $config->min_value) {
            throw new ValidationException(
                "El valor debe ser mayor o igual a {$config->min_value}"
            );
        }

        if ($config->max_value !== null && $numValue > $config->max_value) {
            throw new ValidationException(
                "El valor debe ser menor o igual a {$config->max_value}"
            );
        }
    }

    /**
     * Validar opciones permitidas
     */
    protected function validateOptions(SystemConfig $config, $value): void
    {
        $options = $config->options;

        if (empty($options) || !is_array($options)) {
            return;
        }

        // Si las opciones son un array de objetos con 'value'
        $allowedValues = array_map(function ($option) {
            return is_array($option) && isset($option['value']) ? $option['value'] : $option;
        }, $options);

        if (!in_array($value, $allowedValues, true)) {
            throw new ValidationException(
                "El valor debe ser una de las opciones permitidas: " . implode(', ', $allowedValues)
            );
        }
    }

    /**
     * Validar longitud de string
     */
    protected function validateLength(SystemConfig $config, $value): void
    {
        if (!is_string($value)) {
            return;
        }

        $length = mb_strlen($value);

        if ($config->min_value !== null && $length < $config->min_value) {
            throw new ValidationException(
                "La longitud debe ser de al menos {$config->min_value} caracteres"
            );
        }

        if ($config->max_value !== null && $length > $config->max_value) {
            throw new ValidationException(
                "La longitud no debe exceder {$config->max_value} caracteres"
            );
        }
    }

    /**
     * Verificar si es JSON válido
     */
    protected function isValidJson($value): bool
    {
        if (is_array($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Verificar si es fecha válida
     */
    protected function isValidDate($value): bool
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        try {
            $date = \Carbon\Carbon::parse($value);
            return $date->isValid();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar si es fecha-hora válida
     */
    protected function isValidDateTime($value): bool
    {
        return $this->isValidDate($value);
    }

    /**
     * Sanitizar un valor antes de guardarlo
     */
    public function sanitize(SystemConfig $config, $value)
    {
        return match ($config->value_type) {
            ValueTypeEnum::STRING => strip_tags(trim($value)),
            ValueTypeEnum::INTEGER => intval($value),
            ValueTypeEnum::FLOAT => floatval($value),
            ValueTypeEnum::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            ValueTypeEnum::JSON => is_string($value) ? $value : json_encode($value),
            ValueTypeEnum::EMAIL => filter_var(trim($value), FILTER_SANITIZE_EMAIL),
            ValueTypeEnum::URL => filter_var(trim($value), FILTER_SANITIZE_URL),
            default => $value,
        };
    }
}
