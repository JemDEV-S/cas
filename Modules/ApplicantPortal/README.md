# 🎯 Módulo ApplicantPortal

Portal web para postulantes del Sistema de Convocatorias CAS de la Municipalidad de San Jerónimo.

## 📋 Descripción

El módulo **ApplicantPortal** proporciona la interfaz de usuario para que los ciudadanos puedan:

- Ver convocatorias activas
- Postular a vacantes disponibles
- Gestionar sus postulaciones
- Administrar su perfil profesional
- Subir documentos requeridos
- Dar seguimiento a evaluaciones

## 🏗️ Arquitectura

Este módulo es parte de la **Fase 7: Frontend** del roadmap del sistema. Consume servicios de los siguientes módulos core:

- **Application** - Gestión de postulaciones
- **JobPosting** - Información de convocatorias
- **User** - Perfil del postulante
- **Document** - Gestión documental
- **Notification** - Notificaciones

## 📁 Estructura de Archivos

```
ApplicantPortal/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php      # Dashboard principal
│   │       ├── ApplicationController.php    # Gestión de postulaciones
│   │       ├── JobPostingController.php     # Convocatorias y postulación
│   │       └── ProfileController.php        # Perfil del postulante
│   └── Providers/
│       ├── ApplicantPortalServiceProvider.php
│       ├── RouteServiceProvider.php
│       └── EventServiceProvider.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── layouts/
│       │       └── master.blade.php         # Layout principal
│       ├── dashboard.blade.php              # Vista del dashboard
│       ├── job-postings/                    # Vistas de convocatorias
│       ├── applications/                    # Vistas de postulaciones
│       └── profile/                         # Vistas de perfil
├── routes/
│   ├── web.php                              # Rutas web del portal
│   └── api.php                              # API endpoints (futuro)
└── README.md
```

## 🛣️ Rutas Principales

### Dashboard
- `GET /portal/dashboard` - Dashboard principal del postulante

### Convocatorias
- `GET /portal/convocatorias` - Listar convocatorias activas
- `GET /portal/convocatorias/{id}` - Ver detalle de convocatoria
- `GET /portal/convocatorias/{postingId}/postular/{profileId}` - Formulario de postulación
- `POST /portal/convocatorias/{postingId}/postular/{profileId}` - Enviar postulación

### Mis Postulaciones
- `GET /portal/postulaciones` - Listar mis postulaciones
- `GET /portal/postulaciones/{id}` - Ver detalle de postulación
- `POST /portal/postulaciones/{id}/desistir` - Desistir de postulación
- `GET /portal/postulaciones/{id}/documentos/{documentId}` - Descargar documento

### Mi Perfil
- `GET /portal/perfil` - Ver perfil
- `GET /portal/perfil/editar` - Formulario de edición
- `PUT /portal/perfil/actualizar` - Actualizar datos personales
- `GET /portal/perfil/contrasena` - Cambiar contraseña
- `PUT /portal/perfil/contrasena` - Actualizar contraseña
- `GET /portal/perfil/formacion` - Gestionar formación académica
- `GET /portal/perfil/experiencia` - Gestionar experiencia laboral
- `GET /portal/perfil/cursos` - Gestionar cursos y certificaciones
- `GET /portal/perfil/documentos` - Gestionar documentos personales
- `POST /portal/perfil/documentos` - Subir documento
- `DELETE /portal/perfil/documentos/{documentId}` - Eliminar documento

## 🎨 Características de UI

### Diseño Municipal
- **Colores institucionales**:
  - Azul Municipal: `#3484A5`
  - Verde Municipal: `#2CA792`
  - Amarillo Municipal: `#F0C84F`

### Mascota "Jerónimo"
- Vicuña animada en SVG que guía al usuario
- Animaciones CSS personalizadas (float, wave)
- Mensajes contextuales de ayuda

### Componentes
- Dashboard con estadísticas en tiempo real
- Tarjetas de postulaciones recientes
- Sistema de filtros y búsqueda
- Calendario de fechas importantes
- Indicador de completitud de perfil
- Botones de acciones rápidas

### Responsive Design
- Mobile-first approach
- Breakpoints: sm (640px), md (768px), lg (1024px)
- Componentes adaptables a cualquier pantalla

## 🔐 Seguridad

### Middleware Aplicado
```php
['auth', 'role:applicant']
```

Todas las rutas requieren:
1. **Autenticación** - Usuario debe estar logueado
2. **Rol de Postulante** - Solo usuarios con rol `applicant`

### Validaciones
- FormRequests para validación de datos
- Verificación de propiedad de recursos
- Prevención de acceso no autorizado
- CSRF protection en formularios

## 📊 Integración con Servicios

### ApplicationService
```php
// Obtener postulaciones del usuario
$myApplications = $this->applicationService->getUserApplications($user->id);

// Crear nueva postulación
$application = $this->applicationService->createApplication($data);

// Desistir de postulación
$this->applicationService->withdrawApplication($id, $user->id);
```

### JobPostingService
```php
// Obtener convocatorias activas
$postings = $this->jobPostingService->getActivePostings($filters);

// Obtener detalle de convocatoria
$posting = $this->jobPostingService->getJobPostingById($id);

// Obtener perfiles de puesto
$jobProfiles = $this->jobPostingService->getJobProfiles($postingId);
```

### UserService
```php
// Actualizar perfil
$this->userService->updateProfile($userId, $data);

// Cambiar contraseña
$this->userService->updatePassword($userId, $newPassword);

// Subir documento
$this->userService->uploadDocument($userId, $documentData);
```

## 🚀 Uso

### Acceder al Portal
1. El usuario debe estar registrado y tener rol `applicant`
2. Iniciar sesión en el sistema
3. Navegar a `/portal/dashboard`

### Postular a una Convocatoria
1. Ver convocatorias en `/portal/convocatorias`
2. Seleccionar convocatoria de interés
3. Ver perfiles disponibles
4. Hacer clic en "Postular"
5. Completar formulario con documentos
6. Aceptar términos y condiciones
7. Enviar postulación

### Seguimiento de Postulación
1. Ir a `/portal/postulaciones`
2. Ver listado de todas las postulaciones
3. Filtrar por estado (Activa, Aprobada, En Evaluación, etc.)
4. Ver detalle de cada postulación
5. Descargar documentos generados

## 📈 Estados de Postulación

| Estado | Descripción | Acciones Disponibles |
|--------|-------------|---------------------|
| PRESENTADA | Postulación enviada | Desistir, Ver |
| EN_REVISION | En revisión de elegibilidad | Ver |
| APTO | Postulante apto para continuar | Ver |
| NO_APTO | Postulante no cumple requisitos | Ver motivos |
| EN_EVALUACION | En proceso de evaluación | Ver, Esperar resultados |
| SUBSANACION | Requiere subsanar documentos | Subir documentos |
| APROBADA | Postulación aprobada | Ver, Descargar contrato |
| RECHAZADA | Postulación rechazada | Ver motivos |
| DESISTIDA | Usuario desistió | - |

## 🎯 Próximas Funcionalidades

### En Desarrollo
- [ ] Sistema de notificaciones en tiempo real
- [ ] Chat de soporte con RRHH
- [ ] Firma digital de documentos
- [ ] Simulador de puntaje

### Planificado
- [ ] App móvil (PWA)
- [ ] Recordatorios por email/SMS
- [ ] Historial de postulaciones anteriores
- [ ] Recomendaciones de convocatorias

## 📞 Soporte

Para reportar problemas o solicitar funcionalidades:
- Email: oti@munisanjeronimo.gob.pe
- Tel: (084) 123-4567
- Horario: Lunes a Viernes, 8:00 AM - 5:00 PM

## 📄 Licencia

Propiedad de la Municipalidad Distrital de San Jerónimo - Cusco.
Todos los derechos reservados © 2025
