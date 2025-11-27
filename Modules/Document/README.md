# Módulo Document - Gestión Documental y Firma Digital

## Descripción

Módulo completo para la gestión documental centralizada y firma digital integrada con **FIRMA PERÚ** (Plataforma Nacional de Firma Digital del Gobierno Peruano).

## Características Principales

✅ Generación automática de documentos desde templates
✅ Integración completa con FIRMA PERÚ
✅ Flujos de firma secuenciales y paralelos
✅ Auditoría completa de documentos
✅ Generación de PDFs
✅ Almacenamiento seguro
✅ Múltiples formatos de firma (PAdES, XAdES, CAdES)

## Instalación

El módulo ya ha sido instalado y configurado. Las migraciones y seeders se ejecutaron exitosamente.

## Configuración

### 1. Credenciales FIRMA PERÚ

Coloca tu archivo `fwAuthorization.json` en:
```
storage/app/firmaperu/fwAuthorization.json
```

Estructura del archivo:
```json
{
  "client_id": "TU_CLIENT_ID",
  "client_secret": "TU_CLIENT_SECRET",
  "token_url": "https://api.firmaperu.gob.pe/token"
}
```

### 2. Variables de Entorno

Agrega en tu `.env`:

```env
# FIRMA PERÚ
FIRMAPERU_CREDENTIALS_PATH=storage/app/firmaperu/fwAuthorization.json
FIRMAPERU_LOCAL_PORT=48596
FIRMAPERU_SIGNATURE_LEVEL=B
FIRMAPERU_THEME=claro

# TSA (Timestamp Authority) - Opcional
FIRMAPERU_TSA_URL=
FIRMAPERU_TSA_USER=
FIRMAPERU_TSA_PASSWORD=

# Storage
DOCUMENT_STORAGE_DISK=private
DOCUMENT_STORAGE_PATH=documents
```

### 3. Instalar dompdf (si no está instalado)

```bash
composer require barryvdh/laravel-dompdf
```

## Uso

### Generar Documento desde JobProfile

Cuando un `JobProfile` es aprobado, **automáticamente se genera un documento** con los datos del perfil:

```php
// El evento JobProfileApproved dispara la generación automática
event(new JobProfileApproved($jobProfile, $approvedBy));
```

### Generar Documento Manualmente

```php
use Modules\Document\Services\DocumentService;
use Modules\Document\Entities\DocumentTemplate;

$documentService = app(DocumentService::class);
$template = DocumentTemplate::where('code', 'TPL_JOB_PROFILE')->first();

$document = $documentService->generateFromTemplate(
    $template,
    $jobProfile,
    [
        'title' => 'Perfil de Puesto - ' . $jobProfile->title,
        // ... datos adicionales
    ]
);
```

### Crear Flujo de Firmas

```php
use Modules\Document\Services\SignatureService;

$signatureService = app(SignatureService::class);

$signers = [
    ['user_id' => $reviewerId, 'type' => 'visto_bueno', 'role' => 'Revisor'],
    ['user_id' => $approverId, 'type' => 'aprobacion', 'role' => 'Aprobador'],
];

$workflow = $signatureService->createWorkflow(
    $document,
    $signers,
    'sequential' // o 'parallel'
);
```

### Firmar un Documento

1. El usuario accede a: `/documents/{document}/sign`
2. Se muestra el visor del PDF y el botón "Iniciar Firma"
3. Al hacer clic, se abre el componente web de FIRMA PERÚ
4. El usuario selecciona su certificado digital (DNIe o certificado instalado)
5. El documento se firma y se sube automáticamente al servidor
6. El flujo avanza al siguiente firmante

## Estructura del Módulo

```
Modules/Document/
├── app/
│   ├── Entities/              # Modelos
│   │   ├── DocumentTemplate.php
│   │   ├── GeneratedDocument.php
│   │   ├── DigitalSignature.php
│   │   ├── SignatureWorkflow.php
│   │   └── DocumentAudit.php
│   ├── Services/              # Servicios principales
│   │   ├── DocumentService.php          # Generación de documentos
│   │   ├── FirmaPeruService.php         # Integración FIRMA PERÚ
│   │   ├── SignatureService.php         # Gestión de firmas
│   │   └── TemplateRendererService.php  # Renderizado de templates
│   ├── Http/Controllers/
│   │   ├── DocumentController.php
│   │   └── DocumentSignatureController.php
│   ├── Events/                # Eventos
│   │   ├── DocumentGenerated.php
│   │   ├── DocumentReadyForSignature.php
│   │   ├── DocumentSigned.php
│   │   └── SignatureRejected.php
│   └── Listeners/
│       └── GenerateJobProfileDocument.php  # Genera doc al aprobar perfil
├── database/
│   ├── migrations/            # 5 migraciones
│   └── seeders/               # Seeders de templates
├── resources/
│   └── views/
│       ├── templates/         # Templates de documentos
│       │   └── job_profile.blade.php
│       └── sign/              # Vista de firma
│           └── index.blade.php
├── routes/
│   ├── web.php               # Rutas web
│   └── api.php               # Rutas API (para FIRMA PERÚ)
└── config/
    └── config.php            # Configuración del módulo
```

## Templates de Documentos

### Templates Disponibles

- **TPL_JOB_PROFILE**: Perfil de Puesto ✅ Activo
- **TPL_CONVOCATORIA**: Bases de Convocatoria (por implementar)
- **TPL_ACTA**: Acta de Evaluación (por implementar)

### Crear Nuevo Template

```php
DocumentTemplate::create([
    'code' => 'TPL_CUSTOM',
    'name' => 'Mi Template Personalizado',
    'category' => 'otro',
    'content' => '<html>...</html>',  // HTML con sintaxis Blade
    'variables' => ['var1', 'var2'],
    'signature_required' => true,
    'signature_workflow_type' => 'sequential',
    'status' => 'active',
]);
```

## Integración con FIRMA PERÚ

### Formatos de Firma Soportados

1. **PAdES** (PDF Advanced Electronic Signatures)
   - Para documentos PDF
   - Firma visible con estampado
   - Niveles: B, T, LTA

2. **XAdES** (XML Advanced Electronic Signatures)
   - Para documentos XML
   - Firma enveloped

3. **CAdES** (CMS Advanced Electronic Signatures)
   - Firma desacoplada
   - Para cualquier tipo de archivo

### Configuración de Firma

```php
$params = [
    'signatureFormat' => 'PAdES',
    'signatureLevel' => 'B',         // B | T | LTA
    'visiblePosition' => true,        // Mostrar visor de posicionamiento
    'signatureStyle' => 1,            // 0-4 estilos de estampado
    'stampPage' => 1,                 // Página donde firmar
    'role' => 'Jefe de RR.HH.',      // Rol del firmante
];
```

## Rutas Principales

### Web
- `GET /documents` - Listado de documentos
- `GET /documents/pending-signatures` - Documentos pendientes de firma
- `GET /documents/{document}` - Ver documento
- `GET /documents/{document}/download` - Descargar PDF
- `GET /documents/{document}/sign` - Firmar documento

### API (Para FIRMA PERÚ)
- `POST /api/documents/signature-params` - Obtener parámetros de firma
- `GET /api/documents/{document}/download-for-signature` - Descargar para firmar
- `POST /api/documents/{document}/upload-signed` - Subir documento firmado

## Eventos y Listeners

### Eventos Disponibles

- `DocumentGenerated` - Cuando se genera un documento
- `DocumentReadyForSignature` - Cuando está listo para firmar
- `DocumentSigned` - Cuando se firma exitosamente
- `SignatureRejected` - Cuando se rechaza una firma

### Suscribirse a Eventos

```php
// En EventServiceProvider
protected $listen = [
    DocumentSigned::class => [
        SendSignatureNotification::class,
    ],
];
```

## Seguridad

- ✅ Tokens de un solo uso para descarga/subida
- ✅ Validación de certificados digitales
- ✅ Auditoría completa de acciones
- ✅ Almacenamiento privado de PDFs
- ✅ Validación de permisos por políticas

## Auditoría

Todas las acciones quedan registradas:

```php
use Modules\Document\Entities\DocumentAudit;

$audits = DocumentAudit::where('generated_document_id', $documentId)
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();
```

Acciones registradas:
- `created` - Documento generado
- `viewed` - Documento visualizado
- `downloaded` - Documento descargado
- `signed` - Documento firmado
- `rejected` - Firma rechazada
- `deleted` - Documento eliminado

## Próximas Mejoras

- [ ] Templates para Convocatoria y Actas
- [ ] Firma en lote (múltiples documentos)
- [ ] Verificación de firmas digitales
- [ ] Exportar historial de auditoría
- [ ] Dashboard de documentos
- [ ] Notificaciones automáticas

## Soporte

Para dudas sobre FIRMA PERÚ, consultar:
- Documentación oficial: https://www.gob.pe/firmaperu
- PDF de integración incluido en el proyecto

## Licencia

Propiedad del proyecto CAS-MDSJ
