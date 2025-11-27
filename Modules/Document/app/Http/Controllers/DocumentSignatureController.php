<?php

namespace Modules\Document\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Document\Services\FirmaPeruService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Services\DocumentService;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentSignatureController extends Controller
{
    public function __construct(
        protected FirmaPeruService $firmaPeruService,
        protected SignatureService $signatureService,
        protected DocumentService $documentService
    ) {}

    /**
     * Muestra la vista de firma digital
     */
    public function index(GeneratedDocument $document)
    {
        $this->authorize('sign', $document);

        // Verificar que el documento requiere firma
        if (!$document->requiresSignature()) {
            return redirect()->back()->with('error', 'Este documento no requiere firma digital.');
        }

        // Verificar que el usuario puede firmar
        if (!$document->canBeSignedBy(auth()->id())) {
            return redirect()->back()->with('error', 'No tiene permisos para firmar este documento en este momento.');
        }

        $signature = DigitalSignature::where('generated_document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $workflow = $document->signatureWorkflow()->first();

        return view('document::sign.index', compact('document', 'signature', 'workflow'));
    }

    /**
     * API: Obtiene los parámetros de firma para FIRMA PERÚ
     * Este endpoint es llamado por el componente web de FIRMA PERÚ
     */
    public function getSignatureParams(Request $request)
    {
        // Validar token de parámetros
        $token = $request->input('param_token');

        if (!$token) {
            return response()->json(['error' => 'Token no válido'], 401);
        }

        // Obtener el ID del documento y firma desde la sesión o cache
        $documentId = session("signature_doc_{$token}");
        $signatureId = session("signature_sig_{$token}");

        if (!$documentId || !$signatureId) {
            return response()->json(['error' => 'Sesión expirada'], 401);
        }

        $document = GeneratedDocument::findOrFail($documentId);
        $signature = DigitalSignature::findOrFail($signatureId);

        // Preparar parámetros de firma
        $params = $this->firmaPeruService->prepareSignatureParams($document, $signature);

        // Retornar parámetros codificados en Base64
        return response()->json(
            base64_encode(json_encode($params))
        );
    }

    /**
     * API: Descarga el documento para firma
     * Endpoint llamado por FIRMA PERÚ para obtener el PDF
     */
    public function downloadForSignature(GeneratedDocument $document, Request $request)
    {
        $token = $request->input('token');

        // Validar token
        $validDocId = $this->firmaPeruService->validateDownloadToken($token);

        if ($validDocId !== $document->id) {
            abort(403, 'Token no válido');
        }

        // Retornar el PDF
        $path = $document->pdf_path;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404, 'Documento no encontrado');
        }

        return Storage::disk('private')->response($path, $document->code . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * API: Recibe el documento firmado desde FIRMA PERÚ
     */
    public function uploadSigned(Request $request, GeneratedDocument $document)
    {
        $token = $request->input('token');

        // Validar token
        $validSigId = $this->firmaPeruService->validateUploadToken($token);

        if (!$validSigId) {
            return response()->json(['error' => 'Token no válido'], 401);
        }

        $signature = DigitalSignature::findOrFail($validSigId);

        // Validar que la firma pertenece al documento
        if ($signature->generated_document_id !== $document->id) {
            return response()->json(['error' => 'Firma no corresponde al documento'], 400);
        }

        // Validar archivo firmado
        if (!$request->hasFile('signed_file')) {
            return response()->json(['error' => 'Archivo firmado no recibido'], 400);
        }

        $signedFile = $request->file('signed_file');

        // Procesar el documento firmado
        $this->firmaPeruService->processSignedDocument($document, $signature, $signedFile);

        // Procesar la firma en el workflow
        $this->signatureService->processSignature($signature);

        return response()->json([
            'success' => true,
            'message' => 'Documento firmado exitosamente',
        ]);
    }

    /**
     * Cancela una firma
     */
    public function cancel(GeneratedDocument $document, Request $request)
    {
        $this->authorize('sign', $document);

        $signature = DigitalSignature::where('generated_document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $reason = $request->input('reason', 'Firma cancelada por el usuario');

        $this->signatureService->rejectSignature($signature, $reason);

        return redirect()->route('documents.index')
            ->with('success', 'Firma cancelada exitosamente.');
    }

    /**
     * Genera imagen de sello/estampado para la firma
     */
    public function getSignatureStamp(Request $request)
    {
        $userId = $request->input('user');
        $user = \Modules\User\Entities\User::findOrFail($userId);

        // Generar imagen de sello institucional
        // Por ahora retornar una imagen por defecto
        $defaultStamp = public_path('images/sello-institucional.png');

        if (file_exists($defaultStamp)) {
            return response()->file($defaultStamp);
        }

        // Si no existe, crear un sello simple con GD
        return $this->generateDefaultStamp($user);
    }

    /**
     * Genera un sello por defecto con GD
     */
    protected function generateDefaultStamp($user)
    {
        $width = 200;
        $height = 100;

        $image = imagecreate($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        $text = $user->name;
        imagestring($image, 5, 10, 40, $text, $textColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return response($imageData)->header('Content-Type', 'image/png');
    }
}
