<?php

declare(strict_types=1);

namespace Modules\JobPosting\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Document\Events\DocumentFullySigned;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Events\JobPostingPublished;

class PublishJobPostingAfterSignatures
{
    public function handle(DocumentFullySigned $event): void
    {
        $document = $event->document;

        // Verificar que el documento pertenece a un JobPosting
        if (!$document->documentable instanceof JobPosting) {
            return;
        }

        $jobPosting = $document->documentable;

        // Verificar que está en estado EN_FIRMA
        if ($jobPosting->status !== JobPostingStatusEnum::EN_FIRMA) {
            Log::warning('JobPosting no está en estado EN_FIRMA', [
                'job_posting_id' => $jobPosting->id,
                'current_status' => $jobPosting->status->value,
                'document_id' => $document->id,
            ]);
            return;
        }

        // Cambiar estado a PUBLICADA
        $jobPosting->status = JobPostingStatusEnum::PUBLICADA;
        $jobPosting->published_at = now();
        $jobPosting->save();

        // Disparar evento de publicación
        event(new JobPostingPublished($jobPosting));

        Log::info('Convocatoria publicada después de firmas completas', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'published_at' => $jobPosting->published_at->format('d/m/Y H:i:s'),
            'signatures_completed' => $document->signatures_completed,
        ]);
    }
}
