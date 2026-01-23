<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Application\Services\ApplicationService;
use Modules\Application\Entities\Application;
use Modules\Document\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Application\Entities\ApplicationDocument;
use Modules\Application\Enums\ApplicationStatus;


class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Display a listing of the user's applications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $status = $request->get('status');
        $search = $request->get('search');

        // Get applications with filters
        $applications = $this->applicationService->getUserApplications(
            $user->id,
            $status,
            $search
        );

        // Get status counts for filter badges
        $statusCounts = $this->applicationService->getUserApplicationStatusCounts($user->id);

        // Agregar información de fase actual a cada postulación para determinar si puede subir CV
        $applications->getCollection()->transform(function ($application) {
            $application->load('eligibilityOverride');

            // Verificar si tiene un reclamo aprobado que lo haga apto
            $hasApprovedClaim = $application->eligibilityOverride
                && $application->eligibilityOverride->isApproved()
                && $application->eligibilityOverride->new_status === 'APTO';

            if ($hasApprovedClaim) {
                $jobPosting = $application->jobProfile?->jobPosting;
                if ($jobPosting) {
                    $currentPhase = $jobPosting->getCurrentPhase();
                    $application->can_upload_cv = $currentPhase && in_array($currentPhase->phase?->code ?? '', ['PHASE_05_CV_SUBMISSION', 'PHASE_05_DOCUMENTS']);
                } else {
                    $application->can_upload_cv = false;
                }
            } else {
                $application->can_upload_cv = false;
            }

            return $application;
        });

        return view('applicantportal::applications.index', compact(
            'applications',
            'statusCounts',
            'status',
            'search'
        ));
    }

    /**
     * Display the specified application.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta postulación.');
        }

        // Load necessary relationships
        $application->load([
            'jobProfile.jobPosting.schedules.phase',  // ← ACTUALIZADO: relación directa
            'assignedVacancy',  // ← ACTUALIZADO: vacante asignada si existe
            'academics',
            'experiences',
            'trainings',
            'specialConditions',
            'professionalRegistrations',
            'knowledge',
            'documents',
            'eligibilityOverride'
        ]);

        // Get related entities
        $jobProfile = $application->jobProfile;  // ← ACTUALIZADO
        $jobPosting = $jobProfile->jobPosting;

        // Get current phase using the method instead of a relationship
        $currentPhase = $jobPosting->getCurrentPhase();

        // Verificar si tiene un reclamo aprobado que lo haga apto
        $hasApprovedClaim = $application->eligibilityOverride
            && $application->eligibilityOverride->isApproved()
            && $application->eligibilityOverride->new_status === 'APTO';

        // Verificar si puede subir CV (fase de presentación de CV documentado y reclamo aprobado)
        $canUploadCv = $hasApprovedClaim
            && $currentPhase
            && in_array($currentPhase->phase?->code ?? '', ['PHASE_05_CV_SUBMISSION', 'PHASE_05_DOCUMENTS']);

        return view('applicantportal::applications.show', compact(
            'application',
            'jobProfile',
            'jobPosting',
            'currentPhase',
            'canUploadCv'
        ));
    }

    /**
     * Withdraw an application.
     */
    public function withdraw(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para desistir esta postulación.');
        }

        try {
            $this->applicationService->withdrawApplication($id, $user->id);

            return redirect()
                ->route('applicant.applications.index')
                ->with('success', 'Has desistido de tu postulación exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'No se pudo desistir de la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Download application documents.
     */
    public function downloadDocument(string $id, string $documentId)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para descargar este documento.');
        }

        return $this->applicationService->downloadDocument($documentId);
    }

    public function submit(string $id): RedirectResponse
    {
        try {
            $application = $this->applicationService->submitApplication($id);

            // Generar ficha de postulación PDF
            $this->generateApplicationSheet($application);

            return redirect()
                ->back()
                ->with('success', 'Postulación enviada correctamente')
                ->with('auto_download_pdf', true);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generar ficha de postulación en PDF
     */
    private function generateApplicationSheet(\Modules\Application\Entities\Application $application): void
    {
        try {
            // Cargar todas las relaciones necesarias
            $application->load([
                'applicant',
                'jobProfile.jobPosting',
                'academics.career',
                'experiences',
                'trainings',
                'knowledge',
                'professionalRegistrations',
                'specialConditions'
            ]);

            // Obtener el template
            $template = \Modules\Document\Entities\DocumentTemplate::where('code', 'TPL_APPLICATION_SHEET')
                ->where('status', 'active')
                ->first();

            if (!$template) {
                \Log::warning('Template TPL_APPLICATION_SHEET no encontrado', [
                    'application_id' => $application->id,
                ]);
                return;
            }

            // Preparar datos para el documento
            $data = $this->prepareApplicationSheetData($application);

            // Generar el documento usando el servicio
            $documentService = app(\Modules\Document\Services\DocumentService::class);
            $document = $documentService->generateFromTemplate(
                $template,
                $application,
                $data,
                $application->applicant_id
            );

            \Log::info('Ficha de postulación generada exitosamente', [
                'application_id' => $application->id,
                'document_id' => $document->id,
                'document_code' => $document->code,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar ficha de postulación', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Preparar datos de la postulación para el template
     */
    private function prepareApplicationSheetData(\Modules\Application\Entities\Application $application): array
    {
        $jobProfile = $application->jobProfile;
        $jobPosting = $jobProfile->jobPosting;

        // Calcular edad
        $age = null;
        if ($application->birth_date) {
            $birthDate = \Carbon\Carbon::parse($application->birth_date);
            $age = $birthDate->age;
        }

        return [
            'title' => 'Ficha de Postulación - ' . $application->code,

            // Datos de la postulación
            'application_code' => $application->code,
            'application_date' => $application->application_date?->format('d/m/Y'),

            // Datos de la convocatoria y perfil
            'job_posting_title' => $jobPosting->title ?? 'N/A',
            'job_posting_code' => $jobPosting->code ?? 'N/A',
            'job_profile_name' => $jobProfile->profile_name ?? 'N/A',
            'profile_code' => $jobProfile->code ?? 'N/A',

            // Datos personales
            'full_name' => $application->full_name,
            'dni' => $application->dni,
            'birth_date' => $application->birth_date?->format('d/m/Y'),
            'age' => $age,
            'email' => $application->email,
            'phone' => $application->phone,
            'mobile_phone' => $application->mobile_phone,
            'address' => $application->address,

            // Formación académica
            'academics' => $application->academics->map(function ($academic) {
                $degreeTypeLabel = $academic->degree_type;
                try {
                    if ($academic->degree_type) {
                        $enum = \Modules\JobProfile\Enums\EducationLevelEnum::from($academic->degree_type);
                        $degreeTypeLabel = $enum->label();
                    }
                } catch (\ValueError $e) {
                    $degreeTypeLabel = $academic->degree_type;
                }

                return [
                    'institution_name' => $academic->institution_name,
                    'degree_type' => $academic->degree_type,
                    'degree_type_label' => $degreeTypeLabel,
                    'career_field' => $academic->career?->name ?? $academic->career_field,
                    'degree_title' => $academic->degree_title,
                    'issue_date' => $academic->issue_date?->format('Y'),
                    'is_related_career' => $academic->is_related_career,
                    'related_career_name' => $academic->related_career_name,
                ];
            })->toArray(),

            // Experiencia laboral general (incluye TODAS las experiencias)
            'general_experiences' => $application->experiences->map(function ($experience) {
                return [
                    'organization' => $experience->organization,
                    'position' => $experience->position,
                    'start_date' => $experience->start_date?->format('d/m/Y'),
                    'end_date' => $experience->end_date?->format('d/m/Y'),
                    'duration_days' => $experience->duration_days,
                    'is_specific' => $experience->is_specific,
                    'is_public_sector' => $experience->is_public_sector,
                ];
            })->toArray(),

            // Experiencia laboral específica (solo las específicas)
            'specific_experiences' => $application->experiences->where('is_specific', true)->map(function ($experience) {
                return [
                    'organization' => $experience->organization,
                    'position' => $experience->position,
                    'start_date' => $experience->start_date?->format('d/m/Y'),
                    'end_date' => $experience->end_date?->format('d/m/Y'),
                    'duration_days' => $experience->duration_days,
                    'is_specific' => $experience->is_specific,
                    'is_public_sector' => $experience->is_public_sector,
                ];
            })->values()->toArray(),

            // Calcular totales de experiencia
            'total_general_experience' => $this->calculateExperienceSummary(
                $application->experiences // TODAS las experiencias
            ),
            'total_specific_experience' => $this->calculateExperienceSummary(
                $application->experiences->where('is_specific', true)
            ),

            // Capacitaciones
            'trainings' => $application->trainings->map(function ($training) {
                return [
                    'institution' => $training->institution,
                    'course_name' => $training->course_name,
                    'academic_hours' => $training->academic_hours,
                    'start_date' => $training->start_date?->format('Y-m-d'),
                ];
            })->toArray(),

            // Conocimientos
            'knowledge' => $application->knowledge->map(function ($k) {
                return [
                    'knowledge_name' => $k->knowledge_name,
                    'proficiency_level' => $k->proficiency_level,
                ];
            })->toArray(),

            // Registros profesionales
            'professional_registrations' => $application->professionalRegistrations->map(function ($reg) {
                return [
                    'type' => $reg->registration_type,
                    'number' => $reg->registration_number,
                    'institution' => $reg->issuing_entity,
                    'category' => null,
                    'expiry_date' => $reg->expiry_date?->format('d/m/Y'),
                ];
            })->toArray(),

            // Condiciones especiales
            'special_conditions' => $application->specialConditions->map(function ($condition) {
                return [
                    'type' => $condition->condition_type_name,
                    'bonus_percentage' => $condition->bonus_percentage,
                ];
            })->toArray(),

            // Información adicional
            'ip_address' => $application->ip_address,
            'generation_date' => now()->format('d/m/Y'),
            'generation_time' => now()->format('H:i:s'),
        ];
    }

    /**
     * Calcular resumen de experiencia (total en años, meses y días)
     */
    private function calculateExperienceSummary($experiences): array
    {
        $totalDays = $experiences->sum('duration_days');

        if ($totalDays <= 0) {
            return [
                'total_days' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => '0 días',
            ];
        }

        $years = floor($totalDays / 365);
        $months = floor(($totalDays % 365) / 30);
        $days = $totalDays % 30;

        $parts = [];
        if ($years > 0) $parts[] = "{$years} año" . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = "{$months} mes" . ($months > 1 ? 'es' : '');
        if ($days > 0) $parts[] = "{$days} día" . ($days > 1 ? 's' : '');

        return [
            'total_days' => $totalDays,
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'formatted' => implode(', ', $parts) ?: '0 días',
        ];
    }

    /**
     * Mostrar formulario de subida de CV documentado.
     */
    public function showUploadCvForm(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Verificar que el usuario es dueño de la postulación
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para acceder a esta postulación.');
        }

        // Verificar que la postulación esté en estado APTO
        if ($application->status !== ApplicationStatus::ELIGIBLE) {
            return redirect()
                ->route('applicant.applications.show', $application->id)
                ->with('error', 'Solo puedes subir tu CV documentado cuando tu postulación está en estado APTO.');
        }

        // Cargar relaciones necesarias
        $application->load(['jobProfile.jobPosting.schedules.phase', 'documents', 'eligibilityOverride']);

        // Verificar que tiene un reclamo aprobado
        $hasApprovedClaim = $application->eligibilityOverride
            && $application->eligibilityOverride->isApproved()
            && $application->eligibilityOverride->new_status === 'APTO';

        if (!$hasApprovedClaim) {
            return redirect()
                ->route('applicant.applications.show', $application->id)
                ->with('error', 'Solo puedes subir tu CV documentado si tu reclamo ha sido aprobado y estás en estado APTO.');
        }

        // Verificar que estemos en la fase de presentación de CV
        $jobPosting = $application->jobProfile?->jobPosting;
        $currentPhase = $jobPosting?->getCurrentPhase();
        $canUploadCv = $currentPhase && in_array($currentPhase->phase?->code ?? '', ['PHASE_05_CV_SUBMISSION', 'PHASE_05_DOCUMENTS']);

        if (!$canUploadCv) {
            return redirect()
                ->route('applicant.applications.show', $application->id)
                ->with('error', 'La fase de presentación de CV documentado no está activa en este momento.');
        }

        return view('applicantportal::applications.upload-cv', compact('application'));
    }

    /**
     * Upload CV documentado (solo para postulaciones APTO).
     */
    public function uploadCv(Request $request, string $id): RedirectResponse
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Verificar que el usuario es dueño de la postulación
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para subir documentos a esta postulación.');
        }

        // Verificar que la postulación esté en estado APTO
        if ($application->status !== ApplicationStatus::ELIGIBLE) {
            return redirect()
                ->back()
                ->with('error', 'Solo puedes subir tu CV documentado cuando tu postulación está en estado APTO.');
        }

        // Cargar relaciones necesarias y verificar fase
        $application->load(['jobProfile.jobPosting.schedules.phase', 'eligibilityOverride']);

        // Verificar que tiene un reclamo aprobado
        $hasApprovedClaim = $application->eligibilityOverride
            && $application->eligibilityOverride->isApproved()
            && $application->eligibilityOverride->new_status === 'APTO';

        if (!$hasApprovedClaim) {
            return redirect()
                ->back()
                ->with('error', 'Solo puedes subir tu CV documentado si tu reclamo ha sido aprobado y estás en estado APTO.');
        }
        $jobPosting = $application->jobProfile?->jobPosting;
        $currentPhase = $jobPosting?->getCurrentPhase();
        $canUploadCv = $currentPhase && in_array($currentPhase->phase?->code ?? '', ['PHASE_05_CV_SUBMISSION', 'PHASE_05_DOCUMENTS']);

        if (!$canUploadCv) {
            return redirect()
                ->back()
                ->with('error', 'La fase de presentación de CV documentado no está activa en este momento.');
        }

        // Validar el archivo
        $request->validate([
            'cv_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:15360', // 15 MB en KB
            ],
        ], [
            'cv_file.required' => 'Debes seleccionar un archivo PDF.',
            'cv_file.file' => 'El archivo no es válido.',
            'cv_file.mimes' => 'El archivo debe ser un PDF.',
            'cv_file.max' => 'El archivo no debe superar los 15 MB.',
        ]);

        try {
            $file = $request->file('cv_file');

            // Generar nombre único para el archivo
            $fileName = 'CV_' . $application->code . '_' . time() . '.pdf';
            $filePath = 'applications/' . $application->id . '/cv/' . $fileName;

            // Guardar el archivo
            Storage::disk('local')->put($filePath, file_get_contents($file));

            // Eliminar CV anterior si existe
            $existingCv = $application->documents()->where('document_type', 'DOC_CV')->first();
            if ($existingCv) {
                // Eliminar archivo físico anterior
                if (Storage::disk('local')->exists($existingCv->file_path)) {
                    Storage::disk('local')->delete($existingCv->file_path);
                }
                $existingCv->forceDelete();
            }

            // Crear registro del documento
            ApplicationDocument::create([
                'application_id' => $application->id,
                'document_type' => 'DOC_CV',
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_extension' => 'pdf',
                'file_size' => $file->getSize(),
                'mime_type' => 'application/pdf',
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'description' => 'CV Documentado - Fase 5',
                'uploaded_by' => $user->id,
            ]);

            return redirect()
                ->back()
                ->with('cv_uploaded', true)
                ->with('success', '¡Tu CV documentado ha sido subido correctamente!');

        } catch (\Exception $e) {
            \Log::error('Error al subir CV', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al subir el archivo. Por favor, intenta nuevamente.');
        }
    }

    /**
     * View CV documentado en nueva pestaña.
     */
    public function viewCv(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Verificar que el usuario es dueño de la postulación
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        // Buscar el CV
        $cvDocument = $application->documents()->where('document_type', 'DOC_CV')->first();

        if (!$cvDocument) {
            abort(404, 'No se encontró el CV documentado.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('local')->exists($cvDocument->file_path)) {
            abort(404, 'El archivo no existe en el servidor.');
        }

        // Retornar el PDF para visualización en el navegador
        return response(Storage::disk('local')->get($cvDocument->file_path), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $cvDocument->file_name . '"');
    }

    /**
     * Download application sheet PDF.
     */
    public function downloadPdf(string $id)
    {
        $user = Auth::user();
        $application = $this->applicationService->getApplicationById($id);

        // Check if user owns this application
        if ($application->applicant_id !== $user->id) {
            abort(403, 'No tienes permiso para descargar este documento.');
        }

        // Find the application sheet document
        $document = $application->generatedDocuments()
            ->whereHas('template', fn($q) => $q->where('code', 'TPL_APPLICATION_SHEET'))
            ->first();

        if (!$document) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró la ficha de postulación.');
        }

        // Check if PDF exists
        if (!$document->pdf_path || !Storage::disk('private')->exists($document->pdf_path)) {
            return redirect()
                ->back()
                ->with('error', 'El archivo PDF no existe.');
        }

        return Storage::disk('private')->download(
            $document->pdf_path,
            'Ficha_Postulacion_' . $application->code . '.pdf'
        );
    }
}
