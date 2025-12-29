<?php

declare(strict_types=1);

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\JobPosting\Entities\JobPosting;

class DocumentRegenerationController
{
    /**
     * Regenera documento de convocatoria desde admin
     */
    public function regenerateConvocatoria(Request $request, string $jobPostingId)
    {
        $this->authorize('regenerate-documents');

        $jobPosting = JobPosting::findOrFail($jobPostingId);
        $force = $request->boolean('force', false);

        try {
            // Ejecutar comando
            $exitCode = Artisan::call('convocatoria:regenerate-document', [
                'job-posting-id' => $jobPostingId,
                '--force' => $force,
            ]);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Documento regenerado exitosamente',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar documento',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
