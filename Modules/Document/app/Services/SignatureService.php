<?php

namespace Modules\Document\Services;

use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DigitalSignature;
use Modules\Document\Entities\SignatureWorkflow;
use Modules\Document\Entities\DocumentAudit;
use Modules\Document\Events\DocumentReadyForSignature;
use Modules\Document\Events\DocumentSigned;
use Modules\Document\Events\SignatureRejected;
use Illuminate\Support\Facades\DB;

class SignatureService
{
    /**
     * Crea un flujo de firmas para un documento
     */
    public function createWorkflow(
        GeneratedDocument $document,
        array $signers,
        string $workflowType = 'sequential'
    ): SignatureWorkflow {
        return DB::transaction(function () use ($document, $signers, $workflowType) {
            $totalSteps = count($signers);

            // Crear workflow
            $workflow = SignatureWorkflow::create([
                'generated_document_id' => $document->id,
                'workflow_type' => $workflowType,
                'current_step' => 1,
                'total_steps' => $totalSteps,
                'signers_order' => $signers,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Crear registros de firma para cada firmante
            foreach ($signers as $index => $signer) {
                DigitalSignature::create([
                    'generated_document_id' => $document->id,
                    'user_id' => $signer['user_id'],
                    'signature_type' => $signer['type'] ?? 'firma',
                    'signature_order' => $index + 1,
                    'role' => $signer['role'] ?? null,
                    'status' => 'pending',
                ]);
            }

            // Actualizar documento
            $document->update([
                'total_signatures_required' => $totalSteps,
                'current_signer_id' => $signers[0]['user_id'],
                'signature_status' => 'in_progress',
                'status' => 'pending_signature',
            ]);

            // Notificar al primer firmante
            $firstSigner = $signers[0];
            event(new DocumentReadyForSignature($document, $firstSigner['user_id']));

            DocumentAudit::log(
                $document->id,
                'workflow_created',
                auth()->id(),
                "Flujo de firmas creado: {$workflowType}, {$totalSteps} firmantes"
            );

            return $workflow;
        });
    }

    /**
     * Inicia el proceso de firma para un usuario
     */
    public function initiateSignature(GeneratedDocument $document, string $userId): DigitalSignature
    {
        $signature = DigitalSignature::where('generated_document_id', $document->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        if (!$document->canBeSignedBy($userId)) {
            throw new \Exception('No tiene permisos para firmar este documento en este momento');
        }

        DocumentAudit::log(
            $document->id,
            'signature_initiated',
            $userId,
            'Proceso de firma iniciado'
        );

        return $signature;
    }

    /**
     * Procesa una firma completada
     */
    public function processSignature(
        DigitalSignature $signature,
        array $certificateData = []
    ): void {
        DB::transaction(function () use ($signature, $certificateData) {
            $document = $signature->document;

            // Actualizar la firma
            $signature->update([
                'status' => 'signed',
                'signed_at' => now(),
                'certificate_data' => $certificateData,
                'certificate_issuer' => $certificateData['issuer'] ?? null,
                'certificate_serial' => $certificateData['serial'] ?? null,
                'certificate_valid_from' => $certificateData['valid_from'] ?? null,
                'certificate_valid_to' => $certificateData['valid_to'] ?? null,
                'signature_timestamp' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Actualizar contador de firmas
            $document->increment('signatures_completed');

            // Registrar auditoría
            DocumentAudit::log(
                $document->id,
                'signed',
                $signature->user_id,
                "Documento firmado por {$signature->user->name}"
            );

            // Avanzar al siguiente firmante
            $this->advanceWorkflow($document);

            // Disparar evento
            event(new DocumentSigned($document, $signature));
        });
    }

    /**
     * Avanza el flujo de firmas al siguiente firmante
     */
    protected function advanceWorkflow(GeneratedDocument $document): void
    {
        $workflow = $document->signatureWorkflow()->first();

        if (!$workflow) {
            return;
        }

        // Si ya se completaron todas las firmas
        if ($document->isFullySigned()) {
            $workflow->markAsCompleted();
            $document->update([
                'status' => 'signed',
                'signature_status' => 'completed',
                'current_signer_id' => null,
            ]);

            DocumentAudit::log(
                $document->id,
                'fully_signed',
                auth()->id(),
                'Documento completamente firmado'
            );

            return;
        }

        // Avanzar al siguiente paso
        $workflow->advanceStep();
        $nextSigner = $workflow->getCurrentSigner();

        if ($nextSigner) {
            $document->update([
                'current_signer_id' => $nextSigner['user_id'],
            ]);

            // Notificar al siguiente firmante
            event(new DocumentReadyForSignature($document, $nextSigner['user_id']));

            DocumentAudit::log(
                $document->id,
                'workflow_advanced',
                auth()->id(),
                "Flujo avanzado al paso {$workflow->current_step}"
            );
        }
    }

    /**
     * Rechaza una firma
     */
    public function rejectSignature(
        DigitalSignature $signature,
        string $reason
    ): void {
        DB::transaction(function () use ($signature, $reason) {
            $document = $signature->document;

            // Actualizar la firma
            $signature->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            // Actualizar documento
            $document->update([
                'status' => 'rejected',
                'signature_status' => 'rejected',
            ]);

            // Cancelar el workflow
            $workflow = $document->signatureWorkflow()->first();
            if ($workflow) {
                $workflow->cancel($reason);
            }

            // Registrar auditoría
            DocumentAudit::log(
                $document->id,
                'rejected',
                $signature->user_id,
                "Firma rechazada: {$reason}"
            );

            // Disparar evento
            event(new SignatureRejected($document, $signature, $reason));
        });
    }

    /**
     * Cancela el flujo de firmas
     */
    public function cancelWorkflow(GeneratedDocument $document, string $reason): void
    {
        DB::transaction(function () use ($document, $reason) {
            // Actualizar todas las firmas pendientes
            DigitalSignature::where('generated_document_id', $document->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'rejection_reason' => $reason,
                ]);

            // Actualizar documento
            $document->update([
                'status' => 'cancelled',
                'signature_status' => 'cancelled',
            ]);

            // Cancelar workflow
            $workflow = $document->signatureWorkflow()->first();
            if ($workflow) {
                $workflow->cancel($reason);
            }

            DocumentAudit::log(
                $document->id,
                'workflow_cancelled',
                auth()->id(),
                "Flujo de firmas cancelado: {$reason}"
            );
        });
    }

    /**
     * Obtiene las firmas pendientes de un usuario
     */
    public function getPendingSignatures(string $userId)
    {
        return DigitalSignature::where('user_id', $userId)
            ->where('status', 'pending')
            ->with(['document.template', 'document.documentable'])
            ->get();
    }

    /**
     * Obtiene el historial de firmas de un usuario
     */
    public function getSignatureHistory(string $userId)
    {
        return DigitalSignature::where('user_id', $userId)
            ->where('status', 'signed')
            ->with(['document.template', 'document.documentable'])
            ->orderBy('signed_at', 'desc')
            ->get();
    }
}
