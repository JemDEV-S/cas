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
use Illuminate\Support\Facades\Cache;

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
     * Inicia el proceso de firma digital (guarda token en cache)
     */
    public function startSignature(GeneratedDocument $document, Request $request)
    {
        $this->authorize('sign', $document);

        $request->validate([
            'signature_token' => 'required|string',
            'document_id' => 'required|string',
            'signature_id' => 'required|string',
        ]);

        $token = $request->input('signature_token');
        $documentId = $request->input('document_id');
        $signatureId = $request->input('signature_id');

        // Guardar en cache (TTL: 10 minutos) para que FIRMA PERÚ pueda acceder
        Cache::put("firmaperu_doc_{$token}", $documentId, now()->addMinutes(10));
        Cache::put("firmaperu_sig_{$token}", $signatureId, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => 'Firma iniciada correctamente',
        ]);
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
            \Log::warning('Signature params request without token', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Token requerido'], 400);
        }

        // Obtener el ID del documento y firma desde el cache
        $documentId = Cache::get("firmaperu_doc_{$token}");
        $signatureId = Cache::get("firmaperu_sig_{$token}");

        if (!$documentId || !$signatureId) {
            \Log::warning('Expired or invalid param token', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'document_id_found' => $documentId ? 'yes' : 'no',
                'signature_id_found' => $signatureId ? 'yes' : 'no',
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Token expirado o inválido'], 401);
        }

        try {
            $document = GeneratedDocument::findOrFail($documentId);
            $signature = DigitalSignature::findOrFail($signatureId);

            // Validar que la firma pertenece al documento
            if ((string) $signature->generated_document_id !== (string) $document->id) {
                \Log::error('Signature params mismatch', [
                    'document_id' => $document->id,
                    'signature_doc_id' => $signature->generated_document_id,
                    'signature_id' => $signature->id,
                ]);
                return response()->json(['error' => 'Datos inconsistentes'], 400);
            }

            // Preparar parámetros de firma
            $params = $this->firmaPeruService->prepareSignatureParams($document, $signature);

            \Log::info('Signature params generated', [
                'document_id' => $document->id,
                'document_code' => $document->code,
                'signature_id' => $signature->id,
                'user_id' => $signature->user_id,
                'ip' => $request->ip(),
            ]);

            // Retornar parámetros codificados en Base64
            // FIRMA PERÚ espera texto plano con el base64, no JSON
            $base64Params = base64_encode(json_encode($params));

            return response($base64Params, 200)
                ->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            \Log::error('Error generating signature params', [
                'document_id' => $documentId ?? 'unknown',
                'signature_id' => $signatureId ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Error al generar parámetros de firma',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Descarga el documento para firma
     * Endpoint llamado por FIRMA PERÚ para obtener el PDF
     * IMPORTANTE: No usa route model binding para evitar que se apliquen policies
     */
    public function downloadForSignature(string $document, Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            \Log::warning('Download attempt without token', [
                'document_id' => $document,
                'ip' => $request->ip(),
            ]);
            abort(400, 'Token requerido');
        }

        // Validar token (ya retorna string o null)
        $validDocId = $this->firmaPeruService->validateDownloadToken($token);

        if (!$validDocId) {
            \Log::warning('Invalid or expired download token', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'document_id' => $document,
                'ip' => $request->ip(),
            ]);
            abort(403, 'Token no válido o expirado');
        }

        // Comparación estricta de strings (ambos son UUIDs como strings)
        if ($validDocId !== $document) {
            \Log::error('Token document mismatch', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'expected_doc_id' => $document,
                'token_doc_id' => $validDocId,
                'match' => $validDocId === $document ? 'yes' : 'no',
                'ip' => $request->ip(),
            ]);
            abort(403, 'Token no corresponde al documento solicitado');
        }

        // Obtener el documento manualmente (sin route model binding ni policies)
        $document = GeneratedDocument::findOrFail($validDocId);

        // Retornar el PDF con las firmas más recientes (si existen) o el original
        // CRÍTICO: El segundo firmante debe firmar sobre el PDF que ya tiene la primera firma
        $path = $document->getLatestSignedPath() ?? $document->pdf_path;

        if (!$path || !Storage::disk('private')->exists($path)) {
            \Log::error('PDF file not found for signature', [
                'document_id' => $document->id,
                'pdf_path' => $path,
                'latest_signed_path' => $document->getLatestSignedPath(),
                'original_path' => $document->pdf_path,
                'storage_exists' => $path ? Storage::disk('private')->exists($path) : false,
            ]);
            abort(404, 'Documento PDF no encontrado');
        }

        \Log::info('Document downloaded for signature', [
            'document_id' => $document->id,
            'document_code' => $document->code,
            'path' => $path,
            'has_signatures' => $document->hasAnySignature(),
            'signatures_completed' => $document->signatures_completed,
            'total_required' => $document->total_signatures_required,
            'ip' => $request->ip(),
        ]);

        return Storage::disk('private')->response($path, $document->code . '.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->code . '.pdf"',
        ]);
    }

    /**
     * API: Recibe el documento firmado desde FIRMA PERÚ
     * IMPORTANTE: No usa route model binding para evitar que se apliquen policies
     */
    public function uploadSigned(Request $request, string $document)
    {
        $token = $request->input('token');

        if (!$token) {
            \Log::warning('Upload attempt without token', [
                'document_id' => $document,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Token requerido'], 400);
        }

        // Validar token (retorna string o null, token de un solo uso)
        $validSigId = $this->firmaPeruService->validateUploadToken($token);

        if (!$validSigId) {
            \Log::warning('Invalid or expired upload token', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'document_id' => $document,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Token no válido o expirado'], 401);
        }

        $signature = DigitalSignature::findOrFail($validSigId);

        // Validar que la firma pertenece al documento (comparación estricta de strings)
        if ((string) $signature->generated_document_id !== $document) {
            \Log::error('Signature document mismatch on upload', [
                'signature_id' => $signature->id,
                'signature_doc_id' => (string) $signature->generated_document_id,
                'expected_doc_id' => $document,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Firma no corresponde al documento'], 400);
        }

        // Obtener el documento manualmente (sin route model binding ni policies)
        $document = GeneratedDocument::findOrFail($signature->generated_document_id);

        // Validar archivo firmado
        if (!$request->hasFile('signed_file')) {
            \Log::error('Signed file not received', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Archivo firmado no recibido'], 400);
        }

        $signedFile = $request->file('signed_file');

        // Validar que es un PDF válido
        if ($signedFile->getMimeType() !== 'application/pdf') {
            \Log::error('Invalid signed file type', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'mime_type' => $signedFile->getMimeType(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'El archivo debe ser un PDF'], 400);
        }

        try {
            // Procesar el documento firmado
            $this->firmaPeruService->processSignedDocument($document, $signature, $signedFile);

            // Procesar la firma en el workflow
            // Los datos del certificado ya están en el PDF firmado por FIRMA PERÚ
            $this->signatureService->processSignature($signature, []);

            \Log::info('Document signed successfully', [
                'document_id' => $document->id,
                'document_code' => $document->code,
                'signature_id' => $signature->id,
                'user_id' => $signature->user_id,
                'signature_order' => $signature->signature_order,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento firmado exitosamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error processing signed document', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Error al procesar el documento firmado',
                'message' => $e->getMessage(),
            ], 500);
        }
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
