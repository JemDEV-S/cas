<?php

namespace Modules\Results\Enums;

enum PublicationStatusEnum: string
{
    case DRAFT = 'draft';                          // Borrador inicial
    case PENDING_SIGNATURE = 'pending_signature';  // Esperando firmas de jurados
    case PUBLISHED = 'published';                  // Publicado (firmado y visible)
    case UNPUBLISHED = 'unpublished';              // Despublicado (oculto)
    case CANCELLED = 'cancelled';                  // Cancelado

    /**
     * Obtener el nombre legible del estado
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::PENDING_SIGNATURE => 'Pendiente de Firma',
            self::PUBLISHED => 'Publicado',
            self::UNPUBLISHED => 'Despublicado',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Color para badge en UI
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING_SIGNATURE => 'yellow',
            self::PUBLISHED => 'green',
            self::UNPUBLISHED => 'red',
            self::CANCELLED => 'red',
        };
    }

    /**
     * Verificar si es un estado activo
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PENDING_SIGNATURE, self::PUBLISHED]);
    }

    /**
     * Verificar si puede ser editado
     */
    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Verificar si es visible para postulantes
     */
    public function isVisibleToApplicants(): bool
    {
        return $this === self::PUBLISHED;
    }
}
