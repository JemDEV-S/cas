<?php

namespace Modules\Document\Services;

use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DigitalSignature;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FirmaPeruService
{
    protected string $configPath;
    protected ?array $credentials = null;

    public function __construct()
    {
        $this->configPath = config('document.firmaperu.credentials_path', storage_path('app/firmaperu/fwAuthorization.json'));
    }

    /**
     * Obtiene las credenciales desde el archivo fwAuthorization.json
     */
    protected function getCredentials(): array
    {
        if ($this->credentials) {
            return $this->credentials;
        }

        if (!file_exists($this->configPath)) {
            throw new \Exception('Archivo de credenciales FIRMA PERÚ no encontrado: ' . $this->configPath);
        }

        $content = file_get_contents($this->configPath);
        $this->credentials = json_decode($content, true);

        if (!$this->credentials) {
            throw new \Exception('Error al leer credenciales FIRMA PERÚ');
        }

        return $this->credentials;
    }

    /**
     * Genera un token de acceso para FIRMA PERÚ
     * El token se cachea hasta su expiración
     */
    public function generateToken(): string
    {
        // Intentar obtener token del cache
        $cacheKey = 'firmaperu_token';
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken && $this->isTokenValid($cachedToken)) {
            return $cachedToken;
        }

        // Generar nuevo token
        $credentials = $this->getCredentials();

        $response = Http::asForm()
            ->withOptions(['verify' => false]) // Deshabilitar verificación SSL (solo desarrollo)
            ->post($credentials['token_url'], [
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Error al generar token FIRMA PERÚ: ' . $response->body());
        }

        $token = $response->json('access_token') ?? $response->body();

        // Decodificar el JWT para obtener la expiración
        $tokenParts = explode('.', $token);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            $expiresIn = $payload['exp'] ?? null;

            if ($expiresIn) {
                $ttl = $expiresIn - time() - 60; // 60 segundos de margen
                Cache::put($cacheKey, $token, $ttl);
            }
        }

        return $token;
    }

    /**
     * Verifica si un token JWT es válido
     */
    protected function isTokenValid(string $token): bool
    {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return false;
            }

            $payload = json_decode(base64_decode($tokenParts[1]), true);
            $expiresAt = $payload['exp'] ?? null;

            if (!$expiresAt) {
                return false;
            }

            return time() < $expiresAt;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Prepara los parámetros de firma para FIRMA PERÚ
     * Según la documentación oficial del PDF
     */
    public function prepareSignatureParams(
        GeneratedDocument $document,
        DigitalSignature $signature,
        array $options = []
    ): array {
        $user = $signature->user;
        $token = $this->generateToken();

        // Configuración base para PAdES (PDF)
        $params = [
            'signatureFormat' => 'PAdES',
            'signatureLevel' => $options['signatureLevel'] ?? 'B', // B, T, o LTA
            'signaturePackaging' => 'enveloped',
            'documentToSign' => route('api.documents.download-for-signature', [
                'document' => $document->id,
                'token' => $this->generateDownloadToken($document),
            ]),
            'certificateFilter' => $options['certificateFilter'] ?? '.*', // Solo certificados de firma
            'webTsa' => config('document.firmaperu.tsa_url', ''),
            'userTsa' => config('document.firmaperu.tsa_user', ''),
            'passwordTsa' => config('document.firmaperu.tsa_password', ''),
            'theme' => $options['theme'] ?? 'claro',
            'visiblePosition' => $options['visiblePosition'] ?? true,
            'contactInfo' => '',
            'signatureReason' => $options['signatureReason'] ?? 'Firma del documento: ' . $document->title,
            'bachtOperation' => false,
            'oneByOne' => true,
            'signatureStyle' => $options['signatureStyle'] ?? 1, // 1: estampado + descripción horizontal
            'imageToStamp' => $this->getStampImageUrl($user, $options),
            'stampTextSize' => $options['stampTextSize'] ?? 14,
            'stampWordWrap' => $options['stampWordWrap'] ?? 37,
            'role' => $signature->role ?? $user->position ?? 'Firmante',
            'stampPage' => $options['stampPage'] ?? 1,
            'positionx' => $options['positionx'] ?? 20,
            'positiony' => $options['positiony'] ?? 20,
            'uploadDocumentSigned' => route('api.documents.upload-signed', [
                'document' => $document->id,
                'signature' => $signature->id,
                'token' => $this->generateUploadToken($signature),
            ]),
            'certificationSignature' => $options['certificationSignature'] ?? false,
            'token' => $token,
        ];

        return $params;
    }

    /**
     * Prepara parámetros para firma XML (XAdES)
     */
    public function prepareXAdESParams(GeneratedDocument $document, DigitalSignature $signature): array
    {
        $token = $this->generateToken();

        return [
            'signatureFormat' => 'XAdES',
            'signatureLevel' => 'B',
            'signaturePackaging' => 'enveloped',
            'documentToSign' => route('api.documents.download-for-signature', [
                'document' => $document->id,
                'token' => $this->generateDownloadToken($document),
            ]),
            'certificateFilter' => '.*',
            'webTsa' => config('document.firmaperu.tsa_url', ''),
            'userTsa' => config('document.firmaperu.tsa_user', ''),
            'passwordTsa' => config('document.firmaperu.tsa_password', ''),
            'theme' => 'claro',
            'bachtOperation' => false,
            'uploadDocumentSigned' => route('api.documents.upload-signed', [
                'document' => $document->id,
                'signature' => $signature->id,
                'token' => $this->generateUploadToken($signature),
            ]),
            'token' => $token,
        ];
    }

    /**
     * Prepara parámetros para firma desacoplada (CAdES)
     */
    public function prepareCAdESParams(GeneratedDocument $document, DigitalSignature $signature): array
    {
        $token = $this->generateToken();

        return [
            'signatureFormat' => 'CAdES',
            'signatureLevel' => 'B',
            'signaturePackaging' => 'detached',
            'documentToSign' => route('api.documents.download-for-signature', [
                'document' => $document->id,
                'token' => $this->generateDownloadToken($document),
            ]),
            'certificateFilter' => '.*',
            'webTsa' => config('document.firmaperu.tsa_url', ''),
            'userTsa' => config('document.firmaperu.tsa_user', ''),
            'passwordTsa' => config('document.firmaperu.tsa_password', ''),
            'theme' => 'claro',
            'bachtOperation' => false,
            'uploadDocumentSigned' => route('api.documents.upload-signed', [
                'document' => $document->id,
                'signature' => $signature->id,
                'token' => $this->generateUploadToken($signature),
            ]),
            'token' => $token,
        ];
    }

    /**
     * Procesa el documento firmado recibido desde FIRMA PERÚ
     */
    public function processSignedDocument(
        GeneratedDocument $document,
        DigitalSignature $signature,
        $signedFile
    ): void {
        // Guardar el documento firmado
        $filename = $document->code . '_firmado_' . $signature->signature_order . '.pdf';
        $path = "documents/{$document->id}/signed/{$filename}";

        Storage::disk('private')->put($path, file_get_contents($signedFile->getRealPath()));

        // Actualizar solo la ruta del documento firmado
        // El estado se actualizará en SignatureService::processSignature
        $signature->update([
            'signed_document_path' => $path,
        ]);

        // NOTA: No actualizamos signed_pdf_path aquí porque signatures_completed
        // aún no se ha incrementado. Esto se hace en SignatureService::advanceWorkflow()
    }

    /**
     * Obtiene la URL de la imagen de estampado
     */
    protected function getStampImageUrl($user, array $options): string
    {
        // Si se proporciona una imagen personalizada
        if (isset($options['stampImage'])) {
            return $options['stampImage'];
        }

        // Usar imagen por defecto del sistema
        $defaultStamp = config('document.firmaperu.default_stamp', null);
        if ($defaultStamp) {
            return asset($defaultStamp);
        }

        // URL de una imagen de sello institucional
        return route('api.documents.signature-stamp', [
            'user' => $user->id,
        ]);
    }

    /**
     * Genera un token temporal para descarga del documento
     */
    protected function generateDownloadToken(GeneratedDocument $document): string
    {
        $token = Str::random(64);
        Cache::put("doc_download_{$token}", $document->id, now()->addMinutes(30));
        return $token;
    }

    /**
     * Genera un token temporal para subida del documento firmado
     */
    protected function generateUploadToken(DigitalSignature $signature): string
    {
        $token = Str::random(64);
        Cache::put("sig_upload_{$token}", $signature->id, now()->addMinutes(30));
        return $token;
    }

    /**
     * Valida un token de descarga
     */
    public function validateDownloadToken(string $token): ?string
    {
        return Cache::get("doc_download_{$token}");
    }

    /**
     * Valida un token de subida
     */
    public function validateUploadToken(string $token): ?string
    {
        $signatureId = Cache::get("sig_upload_{$token}");
        if ($signatureId) {
            Cache::forget("sig_upload_{$token}"); // Token de un solo uso
        }
        return $signatureId;
    }
}
