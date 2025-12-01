<?php

namespace Modules\Document\Policies;

use Modules\User\Entities\User;
use Modules\Document\Entities\GeneratedDocument;

class GeneratedDocumentPolicy
{
    /**
     * Ver listado de documentos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('document.view.documents');
    }

    /**
     * Ver un documento específico.
     */
    public function view(User $user, GeneratedDocument $document): bool
    {
        // Permiso global
        if ($user->can('document.view.document')) {
            return true;
        }

        // El creador del documento siempre puede verlo
        if ($user->id === $document->generated_by) {
            return true;
        }

        // Firmantes pueden verlo
        if ($document->signatures()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Firmante actual (workflow)
        if ($document->current_signer_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Subir / crear nuevos documentos.
     */
    public function create(User $user): bool
    {
        return $user->can('document.upload.document');
    }

    /**
     * Actualizar documento (si aplica).
     * Normalmente solo el creador puede editar.
     */
    public function update(User $user, GeneratedDocument $document): bool
    {
        return $user->id === $document->generated_by;
    }

    /**
     * Eliminar documento.
     */
    public function delete(User $user, GeneratedDocument $document): bool
    {
        // Permiso global
        if ($user->can('document.delete.document')) {
            return true;
        }

        // El generador puede eliminar si aún está en borrador
        return $user->id === $document->generated_by && $document->isDraft();
    }

    /**
     * Firmar documento.
     */
    public function sign(User $user, GeneratedDocument $document): bool
    {
        if (!$document->requiresSignature()) {
            return false;
        }

        // Permiso global
        if ($user->can('document.sign.document')) {
            return true;
        }

        // Solo firmantes asignados
        return $document->signatures()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Descargar documento.
     */
    public function download(User $user, GeneratedDocument $document): bool
    {
        if ($user->can('document.download.document')) {
            return true;
        }

        // El creador
        if ($user->id === $document->generated_by) {
            return true;
        }

        // Firmantes
        if ($document->signatures()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Verificar firmas.
     */
    public function verifySignature(User $user): bool
    {
        return $user->can('document.verify.signature');
    }
}
