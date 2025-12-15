<?php

namespace Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Document\Services\DocumentService;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = GeneratedDocument::visibleFor($user)
            ->with(['template', 'generatedBy', 'documentable']);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('signature_status')) {
            $query->where('signature_status', $request->signature_status);
        }

        if ($request->has('template')) {
            $query->where('document_template_id', $request->template);
        }

        // Filtro adicional solo para mis documentos (opcional ahora)
        if ($request->has('my_documents')) {
            $query->where('generated_by', $user->id);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        $templates = DocumentTemplate::active()->get();

        return view('document::documents.index', compact('documents', 'templates'));
    }

    /**
     * Show the specified resource.
     */
    public function show(GeneratedDocument $document)
    {
        $this->authorize('view', $document);

        $document->load([
            'template',
            'generatedBy',
            'documentable',
            'signatures.user',
            'signatureWorkflow',
            'audits.user'
        ]);

        return view('document::documents.show', compact('document'));
    }

    /**
     * Download the document PDF.
     */
    public function download(GeneratedDocument $document, Request $request)
    {
        $this->authorize('view', $document);

        $signed = $request->boolean('signed', false);

        return $this->documentService->download($document, $signed);
    }

    /**
     * View the document PDF in browser.
     */
    public function view(GeneratedDocument $document, Request $request)
    {
        $this->authorize('view', $document);

        $signed = $request->boolean('signed', false);

        return $this->documentService->view($document, $signed);
    }

    /**
     * Regenerate the document PDF.
     */
    public function regenerate(GeneratedDocument $document)
    {
        $this->authorize('update', $document);

        try {
            $this->documentService->regeneratePDF($document);

            return redirect()->back()->with('success', 'Documento regenerado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al regenerar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Regenera un documento de perfil de puesto con datos actualizados
     */
    public function regenerateJobProfile(GeneratedDocument $document)
    {
        $this->authorize('update', $document);

        try {
            // Verificar que sea un documento de perfil de puesto
            if ($document->documentable_type !== 'Modules\JobProfile\Entities\JobProfile') {
                return redirect()->back()->with('error', 'Este documento no es un perfil de puesto.');
            }

            // Cargar el perfil con todas sus relaciones
            $jobProfile = $document->documentable->load([
                'organizationalUnit',
                'requestingUnit.parent',
                'positionCode',
                'requestedBy',
                'reviewedBy',
                'approvedBy'
            ]);

            // Preparar los datos actualizados (misma lógica que el listener)
            $data = $this->prepareJobProfileData($jobProfile);

            // Regenerar el documento con los nuevos datos
            $this->documentService->regenerateDocument($document, $data);

            return redirect()->back()->with('success', 'Documento regenerado exitosamente con los datos actualizados del perfil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al regenerar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos del perfil de puesto para el documento
     */
    protected function prepareJobProfileData($jobProfile): array
    {
        return [
            'title' => mb_strtoupper('Perfil de Puesto - ' . $jobProfile->profile_name),
            'code' => $jobProfile->code,
            'job_profile' => $jobProfile,

            // Datos básicos (en MAYÚSCULAS)
            'profile_title' => mb_strtoupper($jobProfile->title ?? ''),
            'profile_name' => mb_strtoupper($jobProfile->profile_name ?? ''),
            'position_code' => $jobProfile->positionCode?->code,
            'position_name' => mb_strtoupper($jobProfile->positionCode?->name ?? ''),

            // Unidad organizacional (en MAYÚSCULAS)
            'organizational_unit' => mb_strtoupper($jobProfile->organizationalUnit?->name ?? ''),
            'parent_organizational_unit' => mb_strtoupper($jobProfile->requestingUnit?->parent?->name ?? ''),
            'requesting_unit' => mb_strtoupper($jobProfile->requestingUnit?->name ?? ''),
            'required_position' => mb_strtoupper($jobProfile->positionCode?->name ?? ''),

            // Datos del puesto (en MAYÚSCULAS)
            'job_level' => mb_strtoupper($jobProfile->job_level ?? ''),
            'contract_type' => mb_strtoupper($jobProfile->contract_type ?? ''),
            'salary_range' => $jobProfile->salary_range,
            'salary_min' => $jobProfile->salary_min,
            'salary_max' => $jobProfile->salary_max,
            'description' => mb_strtoupper($jobProfile->description ?? ''),
            'mission' => mb_strtoupper($jobProfile->mission ?? ''),
            'working_conditions' => mb_strtoupper($jobProfile->working_conditions ?? ''),

            // Requisitos académicos (en MAYÚSCULAS)
            'education_level' => mb_strtoupper($jobProfile->education_level_label ?? ''),
            'career_field' => mb_strtoupper($jobProfile->career_field ?? ''),
            'title_required' => mb_strtoupper($jobProfile->title_required ?? ''),
            'colegiatura_required' => $jobProfile->colegiatura_required ? 'SÍ' : 'NO',

            // Experiencia (en MAYÚSCULAS)
            'general_experience_years' => mb_strtoupper($jobProfile->general_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
            'specific_experience_years' => mb_strtoupper($jobProfile->specific_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
            'specific_experience_description' => mb_strtoupper($jobProfile->specific_experience_description ?? ''),
            'total_experience_years' => $jobProfile->total_experience_years,

            // Capacitación y conocimientos (arrays convertidos a MAYÚSCULAS)
            'required_courses' => $this->convertArrayToUpperCase($jobProfile->required_courses ?? []),
            'knowledge_areas' => $this->convertArrayToUpperCase($jobProfile->knowledge_areas ?? []),
            'required_competencies' => $this->convertArrayToUpperCase($jobProfile->required_competencies ?? []),

            // Funciones (array convertido a MAYÚSCULAS)
            'main_functions' => $this->convertArrayToUpperCase($jobProfile->main_functions ?? []),

            // Régimen laboral (en MAYÚSCULAS)
            'work_regime' => mb_strtoupper($jobProfile->work_regime_label ?? ''),
            'justification' => mb_strtoupper($jobProfile->justification_text ?? ''),

            // Contrato
            'contract_duration' => mb_strtoupper($jobProfile->contract_duration ?? '3 MESES'),
            'contract_start_date' => $jobProfile->contract_start_date?->format('d/m/Y'),
            'contract_end_date' => $jobProfile->contract_end_date?->format('d/m/Y'),
            'work_location' => mb_strtoupper($jobProfile->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO'),
            'selection_process_name' => mb_strtoupper($jobProfile->selection_process_name ?? 'PROCESO DE SELECCIÓN CAS'),

            // Vacantes
            'total_vacancies' => $jobProfile->total_vacancies,

            // Requisitos generales (desde PositionCode) (en MAYÚSCULAS)
            'requisitos_generales' => mb_strtoupper($jobProfile->getRequisitosGenerales() ?? ''),
            'position_min_experience' => $jobProfile->positionCode?->min_professional_experience,
            'position_specific_experience' => $jobProfile->positionCode?->min_specific_experience,

            // Salario formateado
            'formatted_salary' => $jobProfile->formatted_salary,
            'base_salary' => $jobProfile->positionCode?->base_salary,

            // Aprobación (nombres en MAYÚSCULAS)
            'requested_by' => mb_strtoupper($jobProfile->requestedBy?->getFullNameAttribute() ?? ''),
            'requested_at' => $jobProfile->requested_at?->format('d/m/Y'),
            'reviewed_by' => mb_strtoupper($jobProfile->reviewedBy?->getFullNameAttribute() ?? ''),
            'reviewed_at' => $jobProfile->reviewed_at?->format('d/m/Y'),
            'approved_by' => mb_strtoupper($jobProfile->approvedBy?->getFullNameAttribute() ?? ''),
            'approved_at' => $jobProfile->approved_at?->format('d/m/Y'),

            // Fechas
            'generation_date' => now()->format('d/m/Y'),
            'generation_time' => now()->format('H:i:s'),
            'current_year' => now()->year,

            // Datos estructurados para el Anexo 2
            'anexo2' => $jobProfile->anexo2_data,
            'published_profile' => $jobProfile->published_profile_data,
        ];
    }

    /**
     * Convierte todos los elementos de un array a MAYÚSCULAS
     */
    protected function convertArrayToUpperCase(array $data): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return mb_strtoupper($item);
            }
            if (is_array($item)) {
                return $this->convertArrayToUpperCase($item);
            }
            return $item;
        }, $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneratedDocument $document)
    {
        $this->authorize('delete', $document);

        try {
            $this->documentService->delete($document);

            return redirect()->route('documents.index')->with('success', 'Documento eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los documentos pendientes de firma del usuario
     */
    public function pendingSignatures()
    {
        $pendingDocuments = GeneratedDocument::whereHas('signatures', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('status', 'pending');
        })->with(['template', 'documentable', 'signatures'])->get();

        return view('document::documents.pending-signatures', compact('pendingDocuments'));
    }
}
