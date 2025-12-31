<?php

declare(strict_types=1);

namespace Modules\Jury\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Document\Events\DocumentGenerated;
use Modules\Document\Enums\DocumentCategoryEnum;
use Modules\Jury\Entities\JuryAssignment;
use Modules\Jury\Enums\MemberType;
use Modules\JobPosting\Entities\JobPosting;

class AssignJuriesToSign
{
    public function handle(DocumentGenerated $event): void
    {
        $document = $event->document;

        // Solo procesar documentos de convocatoria completa
        if (!$this->isConvocatoriaDocument($document)) {
            return;
        }

        // Verificar que el documentable sea JobPosting
        if (!$document->documentable instanceof JobPosting) {
            return;
        }

        $jobPosting = $document->documentable;

        // Obtener jurados titulares activos de esta convocatoria ordenados
        $titularJurors = JuryAssignment::where('job_posting_id', $jobPosting->id)
            ->where('member_type', MemberType::TITULAR)
            ->where('is_active', true)
            ->with('juryMember.user')
            ->orderBy('order')
            ->get();

        // Si no hay jurados: publicar directamente sin firmas
        if ($titularJurors->isEmpty()) {
            Log::info('No hay jurados titulares activos. Publicando convocatoria sin firmas', [
                'job_posting_id' => $jobPosting->id,
                'document_id' => $document->id,
            ]);

            $this->publishJobPostingWithoutSignatures($jobPosting, $document);
            return;
        }

        // Si hay jurados pero no hay workflow, algo salió mal
        $workflow = $document->signatureWorkflow;
        if (!$workflow) {
            Log::warning('No se encontró workflow de firmas para el documento con jurados', [
                'document_id' => $document->id,
                'job_posting_id' => $jobPosting->id,
            ]);
            return;
        }

        // Preparar array de firmantes con formato requerido por createWorkflow
        $signers = $titularJurors->map(function($assignment) {
            return [
                'user_id' => $assignment->juryMember->user_id,
                'role' => $assignment->role_in_jury?->label() ?? 'JURADO',
                'type' => 'firma',
            ];
        })->toArray();

        // Actualizar el workflow con los firmantes
        $this->updateWorkflowSigners($workflow, $signers, $document);

        Log::info('Jurados titulares asignados como firmantes', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'signers_count' => count($signers),
            'signers' => $titularJurors->pluck('juryMember.user.name')->toArray(),
        ]);
    }

    /**
     * Actualiza el workflow existente con los firmantes
     */
    private function updateWorkflowSigners($workflow, array $signers, $document): void
    {
        $totalSteps = count($signers);

        // Actualizar workflow
        $workflow->update([
            'total_steps' => $totalSteps,
            'signers_order' => $signers,
        ]);

        // Crear registros de firma para cada firmante
        foreach ($signers as $index => $signer) {
            \Modules\Document\Entities\DigitalSignature::create([
                'generated_document_id' => $document->id,
                'user_id' => $signer['user_id'],
                'signature_type' => $signer['type'] ?? 'firma',
                'signature_order' => $index + 1,
                'role' => $signer['role'] ?? null,
                'status' => 'pending',
            ]);
        }

        // Actualizar documento con el primer firmante
        $document->update([
            'total_signatures_required' => $totalSteps,
            'current_signer_id' => $signers[0]['user_id'],
        ]);

        // Disparar evento para notificar al primer firmante
        event(new \Modules\Document\Events\DocumentReadyForSignature(
            $document,
            $signers[0]['user_id']
        ));
    }

    /**
     * Verifica si el documento es de tipo convocatoria completa
     */
    private function isConvocatoriaDocument($document): bool
    {
        return $document->template
            && $document->template->category === DocumentCategoryEnum::CONVOCATORIA_COMPLETA->value;
    }

    /**
     * Publica la convocatoria directamente sin proceso de firmas
     */
    private function publishJobPostingWithoutSignatures(JobPosting $jobPosting, $document): void
    {
        // Actualizar documento como completado sin firmas
        $document->update([
            'status' => 'generated',
            'signature_status' => 'not_required',
            'total_signatures_required' => 0,
            'signatures_completed' => 0,
            'current_signer_id' => null,
        ]);

        // Verificar que está en estado EN_FIRMA
        if ($jobPosting->status !== \Modules\JobPosting\Enums\JobPostingStatusEnum::EN_FIRMA) {
            Log::warning('JobPosting no está en estado EN_FIRMA, no se puede publicar', [
                'job_posting_id' => $jobPosting->id,
                'current_status' => $jobPosting->status->value,
            ]);
            return;
        }

        // Cambiar estado a PUBLICADA
        $jobPosting->status = \Modules\JobPosting\Enums\JobPostingStatusEnum::PUBLICADA;
        $jobPosting->published_at = now();
        $jobPosting->save();

        // Disparar evento de publicación (esto activará los perfiles automáticamente)
        event(new \Modules\JobPosting\Events\JobPostingPublished($jobPosting));

        Log::info('Convocatoria publicada sin firmas (sin jurados)', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'published_at' => $jobPosting->published_at->format('d/m/Y H:i:s'),
        ]);
    }
}
