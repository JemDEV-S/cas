<?php

namespace Modules\Configuration\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Configuration\Enums\ValueTypeEnum;
use Modules\Configuration\Enums\InputTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasUuid;

class SystemConfig extends BaseModel
{
    use HasUuid;

    protected $table = 'system_configs';

    protected $fillable = [
        'config_group_id',
        'key',
        'value',
        'value_type',
        'default_value',
        'description',
        'validation_rules',
        'options',
        'min_value',
        'max_value',
        'display_name',
        'help_text',
        'display_order',
        'input_type',
        'is_public',
        'required_permission',
        'is_editable',
        'is_system',
        'metadata',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'display_order' => 'integer',
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
        'is_system' => 'boolean',
        'metadata' => 'array',
        'value_type' => ValueTypeEnum::class,
        'input_type' => InputTypeEnum::class,
    ];

    /**
     * Relación con el grupo de configuración
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ConfigGroup::class, 'config_group_id');
    }

    /**
     * Relación con el historial de cambios
     */
    public function history(): HasMany
    {
        return $this->hasMany(ConfigHistory::class, 'system_config_id')->orderByDesc('changed_at');
    }

    /**
     * Obtener el valor parseado según su tipo
     */
    public function getParsedValueAttribute()
    {
        return $this->parseValue($this->value);
    }

    /**
     * Parsear el valor según el tipo
     */
    public function parseValue(?string $value)
    {
        if ($value === null) {
            return $this->parseValue($this->default_value);
        }

        return match ($this->value_type) {
            ValueTypeEnum::DECIMAL, ValueTypeEnum::FLOAT => (float) $value,
            ValueTypeEnum::INTEGER => (int) $value,
            ValueTypeEnum::DECIMAL => (float) $value,
            ValueTypeEnum::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            ValueTypeEnum::JSON => json_decode($value, true),
            ValueTypeEnum::DATE => $value ? \Carbon\Carbon::parse($value) : null,
            ValueTypeEnum::DATETIME => $value ? \Carbon\Carbon::parse($value) : null,
            default => $value,
        };
    }

    /**
     * Scope para configuraciones públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope para configuraciones editables
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Scope para configuraciones de sistema
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope para buscar por grupo
     */
    public function scopeByGroup($query, string $groupCode)
    {
        return $query->whereHas('group', function ($q) use ($groupCode) {
            $q->where('code', $groupCode);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
