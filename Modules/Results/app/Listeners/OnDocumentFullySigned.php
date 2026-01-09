<?php

namespace Modules\Results\Listeners;

use Modules\Document\Events\DocumentFullySigned;
use Modules\Results\Entities\ResultPublication;
use Modules\Results\Enums\PublicationStatusEnum;
use Modules\Results\Jobs\SendResultNotificationsJob;
use Illuminate\Support\Facades\Log;

class OnDocumentFullySigned
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Cuando un documento es completamente firmado,
     * activar automáticamente la publicación de resultados
     */
    public function handle(DocumentFullySigned $event): void
    {
        $document = $event->document;

        // Verificar si este documento pertenece a una publicación de resultados
        $publication = ResultPublication::where('generated_document_id', $document->id)
            ->first();

        if (!$publication) {
            // No es un documento de resultados, ignorar
            return;
        }

        Log::info('Documento de resultados completamente firmado, activando publicación', [
            'publication_id' => $publication->id,
            'document_id' => $document->id,
            'phase' => $publication->phase->value,
        ]);

        try {
            // 1. Cambiar estado a PUBLISHED
            $publication->update([
                'status' => PublicationStatusEnum::PUBLISHED,
                'published_at' => now(),
                'published_by' => auth()->id() ?? $document->current_signer_id,
            ]);

            // 2. Actualizar flag en JobPosting
            $publication->jobPosting->update([
                'results_published' => true,
                'results_published_at' => now(),
                'results_published_by' => auth()->id() ?? $document->current_signer_id,
            ]);

            // 3. Enviar notificaciones a postulantes
            SendResultNotificationsJob::dispatch($publication);

            Log::info('Resultados publicados automáticamente tras firma completa', [
                'publication_id' => $publication->id,
                'phase' => $publication->phase->value,
                'document_id' => $document->id,
                'published_at' => $publication->published_at->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al activar publicación de resultados tras firma', [
                'publication_id' => $publication->id,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // No re-lanzar la excepción para no interrumpir el flujo de firma
            // pero registrar el error para revisión manual
        }
    }
}
