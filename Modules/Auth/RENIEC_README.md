# Servicio de Validación RENIEC - Documentación

## 📋 Descripción

Sistema refactorizado para validación de DNI con la API de PeruDevs (RENIEC), siguiendo las mejores prácticas de arquitectura limpia y SOLID.

## 🏗️ Arquitectura

### Estructura de Carpetas

```
Modules/Auth/app/
├── DTOs/
│   ├── ReniecPersonDataDTO.php          # Datos de persona (inmutable)
│   └── ReniecValidationResultDTO.php    # Resultado de validación
├── Exceptions/
│   ├── ReniecException.php              # Excepción base
│   ├── ReniecApiException.php           # Errores de API
│   ├── ReniecNotFoundException.php      # DNI no encontrado
│   ├── ReniecValidationException.php    # Errores de validación
│   └── ReniecServiceUnavailableException.php
├── Services/Reniec/
│   ├── ReniecApiClient.php              # Cliente HTTP
│   ├── ReniecValidator.php              # Validación de código verificador
│   ├── ReniecCacheService.php           # Gestión de caché
│   └── ReniecService.php                # Servicio principal
├── Http/
│   ├── Controllers/
│   │   └── ReniecValidationController.php
│   ├── Requests/
│   │   ├── ValidateDniRequest.php
│   │   └── ConsultDniRequest.php
│   └── Traits/
│       └── ApiResponses.php
└── config/
    └── reniec.php
```

## ⚙️ Configuración

### 1. Variables de Entorno

Copiar las variables del archivo `.env.reniec.example` a tu `.env`:

```bash
# Habilitar servicio
RENIEC_ENABLED=true

# API de PeruDevs
RENIEC_API_URL=https://api.perudevs.com/api/v1
RENIEC_API_TOKEN=tu_token_aqui

# Configuración HTTP
RENIEC_API_TIMEOUT=10
RENIEC_API_RETRY_TIMES=3
RENIEC_API_RETRY_SLEEP=1000

# Caché
RENIEC_CACHE_ENABLED=true
RENIEC_CACHE_TTL=3600

# Seguridad
RENIEC_MASK_LOGS=true
```

### 2. Obtener Token de API

1. Visitar: https://apiperu.dev/
2. Crear cuenta
3. Obtener API token
4. Configurar en `RENIEC_API_TOKEN`

## 🚀 Uso

### Endpoints Disponibles

#### 1. Validar DNI con Código Verificador

**POST** `/api/auth/validate-dni`

```json
{
  "dni": "12345678",
  "codigo_verificador": "8"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "DNI validado correctamente",
  "data": {
    "first_name": "MARIA ISABEL",
    "last_name": "JIMENEZ DIAZ",
    "full_name": "MARIA ISABEL JIMENEZ DIAZ"
  }
}
```

**Respuesta Error (422):**
```json
{
  "success": false,
  "message": "El código verificador del DNI no coincide. Verifique los datos en su documento.",
  "data": null
}
```

#### 2. Consultar DNI (sin código verificador)

**GET** `/api/auth/consultar-dni/{dni}`

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "DNI encontrado exitosamente",
  "data": {
    "first_name": "MARIA ISABEL",
    "last_name": "JIMENEZ DIAZ",
    "full_name": "MARIA ISABEL JIMENEZ DIAZ"
  }
}
```

#### 3. Verificar Estado del Servicio

**GET** `/api/auth/reniec/status`

```json
{
  "enabled": true,
  "message": "Servicio de RENIEC disponible"
}
```

## 💡 Uso Programático

### Inyección de Dependencias

```php
use Modules\Auth\Services\Reniec\ReniecService;

class MiControlador extends Controller
{
    public function __construct(
        private readonly ReniecService $reniecService
    ) {}

    public function validarDni(Request $request)
    {
        $result = $this->reniecService->validateWithCheckDigit(
            $request->dni,
            $request->codigo_verificador
        );

        if ($result->isValid) {
            // DNI válido
            $personData = $result->personData;
            $nombres = $personData->nombres;
        }
    }
}
```

### Consulta Simple

```php
$personData = $this->reniecService->consultDni('12345678');

if ($personData) {
    echo $personData->nombreCompleto;
    echo $personData->fechaNacimiento;
    echo $personData->genero;
}
```

### Limpiar Caché

```php
// Limpiar caché de un DNI específico
$this->reniecService->clearCache('12345678');

// Limpiar todo el caché de RENIEC
$this->reniecService->flushCache();
```

## 🔒 Seguridad

### Características de Seguridad Implementadas

1. **SSL Habilitado**: No usa `withoutVerifying()` - conexión segura
2. **Logs Enmascarados**: DNIs se registran como `****5678` (cumplimiento LPDP)
3. **Validación Local Primera**: Evita consumir API con datos inválidos
4. **Excepciones Tipadas**: Manejo de errores consistente
5. **Form Requests**: Validación de entrada antes de procesamiento

### Cumplimiento LPDP

El servicio cumple con la Ley de Protección de Datos Personales:
- Datos sensibles enmascarados en logs
- Caché con TTL configurable
- No se almacenan datos permanentemente sin consentimiento

## 🎯 Flujo de Validación

**ESTRATEGIA: API como fuente de verdad**

```
1. Usuario envía DNI + código verificador
   ↓
2. ValidateDniRequest valida formato
   ↓
3. ReniecService recibe petición
   ↓
4. ReniecCacheService busca en caché
   Si existe → Usa caché
   ↓
5. ReniecApiClient consulta API (obtiene datos + código oficial)
   ↓
6. Se cachea resultado exitoso
   ↓
7. ReniecValidator compara código del usuario vs código de la API
   Si coinciden → Válido
   Si no coinciden → Error
   ↓
8. Retorna ReniecValidationResultDTO
```

**Ventajas de este flujo:**
- ✅ API es la fuente oficial y confiable
- ✅ No depende de cálculos locales que pueden fallar
- ✅ Máxima precisión en la validación
- ✅ Caché reduce costos de API para DNIs ya consultados

## 🐛 Manejo de Errores

### Excepciones Disponibles

```php
try {
    $result = $reniecService->validateWithCheckDigit($dni, $codigo);
} catch (ReniecServiceUnavailableException $e) {
    // Servicio deshabilitado
} catch (ReniecNotFoundException $e) {
    // DNI no existe en RENIEC
} catch (ReniecValidationException $e) {
    // Código verificador inválido
} catch (ReniecApiException $e) {
    // Error de comunicación con API
} catch (ReniecException $e) {
    // Cualquier otro error de RENIEC
}
```

### Códigos HTTP

| Código | Significado |
|--------|-------------|
| 200 | Validación exitosa |
| 404 | DNI no encontrado |
| 422 | Validación fallida |
| 503 | Servicio no disponible |
| 500 | Error interno |

## 📊 DTOs (Data Transfer Objects)

### ReniecPersonDataDTO

```php
$personData->dni                  // "12345678"
$personData->nombres              // "MARIA ISABEL"
$personData->apellidoPaterno      // "JIMENEZ"
$personData->apellidoMaterno      // "DIAZ"
$personData->nombreCompleto       // "MARIA ISABEL JIMENEZ DIAZ"
$personData->genero               // "M" o "F"
$personData->fechaNacimiento      // "16/11/1994"
$personData->codigoVerificacion   // "8"

// Métodos útiles
$personData->toArray()            // Array asociativo
$personData->toRegistrationData() // Datos para registro de usuario
$personData->hasCheckDigit('8')   // Verificar código
```

### ReniecValidationResultDTO

```php
$result->isValid       // true/false
$result->message       // Mensaje descriptivo
$result->personData    // ReniecPersonDataDTO o null

// Métodos factory
ReniecValidationResultDTO::success($personData);
ReniecValidationResultDTO::failure('mensaje de error');
```

## 🧪 Testing

### Test Unitario del Validador

```php
$validator = app(ReniecValidator::class);

// Calcular código verificador
$codigo = $validator->calculateCheckDigit('12345678');

// Validar DNI
$isValid = $validator->validate('12345678', '8');
```

### Mock del Servicio

```php
$mock = Mockery::mock(ReniecService::class);
$mock->shouldReceive('validateWithCheckDigit')
     ->with('12345678', '8')
     ->andReturn(ReniecValidationResultDTO::success($personData));

$this->app->instance(ReniecService::class, $mock);
```

## 🔧 Troubleshooting

### Problema: "Servicio no disponible"
**Solución**: Verificar que `RENIEC_ENABLED=true` y `RENIEC_API_TOKEN` esté configurado

### Problema: "Connection timeout"
**Solución**: Aumentar `RENIEC_API_TIMEOUT` o verificar conectividad de red

### Problema: "Token inválido"
**Solución**: Verificar token en https://apiperu.dev/ y regenerar si es necesario

### Problema: Caché no funciona
**Solución**: Verificar driver de caché configurado en `.env` (redis, memcached, etc.)

## 📝 Changelog

### v2.0.0 - Refactorización Completa
- ✅ Arquitectura SOLID
- ✅ DTOs inmutables
- ✅ Excepciones tipadas
- ✅ Form Requests
- ✅ Trait ApiResponses
- ✅ SSL habilitado
- ✅ Logs enmascarados
- ✅ Retry logic con backoff
- ✅ Cache stampede prevention
- ✅ Doble validación de código
- ✅ Validación local antes de API

## 👨‍💻 Mantenimiento

Para agregar nuevas funcionalidades:

1. **Nueva validación**: Extender `ReniecValidator`
2. **Nuevo endpoint**: Agregar método en `ReniecValidationController` con Form Request
3. **Nueva fuente de datos**: Crear nuevo `ApiClient` siguiendo el patrón
4. **Nuevo formato de respuesta**: Crear nuevo DTO

## 📚 Referencias

- API PeruDevs: https://apiperu.dev/
- Documentación Laravel HTTP Client: https://laravel.com/docs/http-client
- DTOs en PHP: https://stitcher.io/blog/laravel-beyond-crud-03-data-transfer-objects
