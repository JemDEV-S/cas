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
