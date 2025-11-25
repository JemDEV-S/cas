<?php

namespace Modules\Organization\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationalUnitClosure extends Model
{
    protected $table = 'organizational_unit_closure';

    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth',
    ];

    public $timestamps = false;

    /**
     * Relación con la unidad ancestro
     */
    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'ancestor_id');
    }

    /**
     * Relación con la unidad descendiente
     */
    public function descendant(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'descendant_id');
    }
}
