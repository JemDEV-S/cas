<?php

namespace Modules\Results\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Results\Enums\PublicationPhaseEnum;
use App\Models\User;

class ResultExport extends Model
{
    use HasUuids;

    protected $fillable = [
        'result_publication_id',
        'format',           // excel, csv, pdf
        'file_path',
        'file_name',
        'file_size',
        'rows_count',
        'exported_by',
        'exported_at',
        'metadata',
    ];

    protected $casts = [
        'exported_at' => 'datetime',
        'metadata' => 'array',
        'file_size' => 'integer',
        'rows_count' => 'integer',
    ];

    /**
     * Relación con la publicación
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(ResultPublication::class, 'result_publication_id');
    }

    /**
     * Usuario que exportó
     */
    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }

    /**
     * Obtener URL de descarga
     */
    public function getDownloadUrl(): string
    {
        return route('admin.results.export.download', $this->id);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
