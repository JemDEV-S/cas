<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'application_id',
        'document_type',
        'file_name',
        'file_path',
        'file_extension',
        'file_size',
        'mime_type',
        'file_hash',
        'requires_signature',
        'is_signed',
        'signature_data',
        'signed_at',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'requires_signature' => 'boolean',
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con usuario que subió el documento
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Relación con usuario que verificó el documento
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    /**
     * Obtener el nombre del tipo de documento
     */
    public function getDocumentTypeNameAttribute(): string
    {
        return match($this->document_type) {
            'DOC_APPLICATION_FORM' => 'Ficha de Postulación',
            'DOC_CV' => 'Curriculum Vitae',
            'DOC_DNI' => 'DNI',
            'DOC_DEGREE' => 'Título/Grado Académico',
            'DOC_CERTIFICATE' => 'Certificado',
            'DOC_EXPERIENCE' => 'Constancia de Experiencia',
            'DOC_SPECIAL_CONDITION' => 'Documento Condición Especial',
            default => $this->document_type,
        };
    }

    /**
     * Obtener URL de descarga del documento
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('application.documents.download', $this->id);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getFormattedSizeAttribute(): string
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

    /**
     * Verificar si el archivo existe en storage
     */
    public function fileExists(): bool
    {
        return Storage::disk('local')->exists($this->file_path);
    }

    /**
     * Eliminar archivo físico del storage
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::disk('local')->delete($this->file_path);
        }
        return false;
    }

    /**
     * Obtener contenido del archivo
     */
    public function getFileContent(): ?string
    {
        if ($this->fileExists()) {
            return Storage::disk('local')->get($this->file_path);
        }
        return null;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al eliminar el documento, eliminar también el archivo físico
        static::deleting(function ($document) {
            if ($document->isForceDeleting()) {
                $document->deleteFile();
            }
        });
    }
}
