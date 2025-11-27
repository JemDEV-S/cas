<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Entities\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // ============================================================
            // AUTH MODULE - Gestión de Autenticación y Autorización
            // ============================================================
            ['name' => 'Ver Lista de Roles', 'slug' => 'auth.view.roles', 'module' => 'auth', 'description' => 'Ver listado completo de roles'],
            ['name' => 'Ver Detalle de Rol', 'slug' => 'auth.view.role', 'module' => 'auth', 'description' => 'Ver información detallada de un rol'],
            ['name' => 'Crear Rol', 'slug' => 'auth.create.role', 'module' => 'auth', 'description' => 'Crear nuevos roles'],
            ['name' => 'Actualizar Rol', 'slug' => 'auth.update.role', 'module' => 'auth', 'description' => 'Modificar roles existentes'],
            ['name' => 'Eliminar Rol', 'slug' => 'auth.delete.role', 'module' => 'auth', 'description' => 'Eliminar roles'],
            ['name' => 'Asignar Rol a Usuario', 'slug' => 'auth.assign.role', 'module' => 'auth', 'description' => 'Asignar roles a usuarios'],
            ['name' => 'Revocar Rol de Usuario', 'slug' => 'auth.revoke.role', 'module' => 'auth', 'description' => 'Quitar roles de usuarios'],

            ['name' => 'Ver Lista de Permisos', 'slug' => 'auth.view.permissions', 'module' => 'auth', 'description' => 'Ver listado de permisos'],
            ['name' => 'Ver Detalle de Permiso', 'slug' => 'auth.view.permission', 'module' => 'auth', 'description' => 'Ver información de un permiso'],
            ['name' => 'Crear Permiso', 'slug' => 'auth.create.permission', 'module' => 'auth', 'description' => 'Crear nuevos permisos'],
            ['name' => 'Actualizar Permiso', 'slug' => 'auth.update.permission', 'module' => 'auth', 'description' => 'Modificar permisos'],
            ['name' => 'Eliminar Permiso', 'slug' => 'auth.delete.permission', 'module' => 'auth', 'description' => 'Eliminar permisos'],
            ['name' => 'Asignar Permiso a Rol', 'slug' => 'auth.assign.permission', 'module' => 'auth', 'description' => 'Asignar permisos a roles'],
            ['name' => 'Revocar Permiso de Rol', 'slug' => 'auth.revoke.permission', 'module' => 'auth', 'description' => 'Quitar permisos de roles'],

            ['name' => 'Ver Sesiones Activas', 'slug' => 'auth.view.sessions', 'module' => 'auth', 'description' => 'Ver sesiones activas de usuarios'],
            ['name' => 'Cerrar Sesión de Usuario', 'slug' => 'auth.terminate.session', 'module' => 'auth', 'description' => 'Cerrar sesión de otro usuario'],
            ['name' => 'Ver Intentos de Login', 'slug' => 'auth.view.loginattempts', 'module' => 'auth', 'description' => 'Ver historial de intentos de login'],

            // ============================================================
            // USER MODULE - Gestión de Usuarios
            // ============================================================
            ['name' => 'Ver Lista de Usuarios', 'slug' => 'user.view.users', 'module' => 'user', 'description' => 'Ver listado de usuarios'],
            ['name' => 'Ver Detalle de Usuario', 'slug' => 'user.view.user', 'module' => 'user', 'description' => 'Ver perfil completo de usuario'],
            ['name' => 'Ver Propio Perfil', 'slug' => 'user.view.own', 'module' => 'user', 'description' => 'Ver su propio perfil'],
            ['name' => 'Crear Usuario', 'slug' => 'user.create.user', 'module' => 'user', 'description' => 'Registrar nuevos usuarios'],
            ['name' => 'Actualizar Usuario', 'slug' => 'user.update.user', 'module' => 'user', 'description' => 'Modificar datos de usuarios'],
            ['name' => 'Actualizar Propio Perfil', 'slug' => 'user.update.own', 'module' => 'user', 'description' => 'Modificar su propio perfil'],
            ['name' => 'Eliminar Usuario', 'slug' => 'user.delete.user', 'module' => 'user', 'description' => 'Eliminar usuarios'],
            ['name' => 'Activar/Desactivar Usuario', 'slug' => 'user.toggle.status', 'module' => 'user', 'description' => 'Cambiar estado activo/inactivo'],
            ['name' => 'Restablecer Contraseña', 'slug' => 'user.reset.password', 'module' => 'user', 'description' => 'Resetear contraseña de usuario'],
            ['name' => 'Gestionar Preferencias', 'slug' => 'user.manage.preferences', 'module' => 'user', 'description' => 'Gestionar preferencias de usuario'],
            ['name' => 'Exportar Usuarios', 'slug' => 'user.export.users', 'module' => 'user', 'description' => 'Exportar lista de usuarios'],

            // ============================================================
            // ORGANIZATION MODULE - Estructura Organizacional
            // ============================================================
            ['name' => 'Ver Estructura Organizacional', 'slug' => 'organization.view.units', 'module' => 'organization', 'description' => 'Ver organigrama'],
            ['name' => 'Ver Unidad Organizacional', 'slug' => 'organization.view.unit', 'module' => 'organization', 'description' => 'Ver detalle de unidad'],
            ['name' => 'Crear Unidad Organizacional', 'slug' => 'organization.create.unit', 'module' => 'organization', 'description' => 'Crear unidades organizacionales'],
            ['name' => 'Actualizar Unidad Organizacional', 'slug' => 'organization.update.unit', 'module' => 'organization', 'description' => 'Modificar unidades'],
            ['name' => 'Eliminar Unidad Organizacional', 'slug' => 'organization.delete.unit', 'module' => 'organization', 'description' => 'Eliminar unidades'],
            ['name' => 'Mover Unidad en Jerarquía', 'slug' => 'organization.move.unit', 'module' => 'organization', 'description' => 'Reorganizar jerarquía'],
            ['name' => 'Exportar Estructura', 'slug' => 'organization.export.structure', 'module' => 'organization', 'description' => 'Exportar organigrama'],

            // ============================================================
            // CONFIGURATION MODULE - Configuración del Sistema
            // ============================================================
            ['name' => 'Ver Configuraciones', 'slug' => 'configuration.view.configs', 'module' => 'configuration', 'description' => 'Ver configuraciones del sistema'],
            ['name' => 'Actualizar Configuración', 'slug' => 'configuration.update.config', 'module' => 'configuration', 'description' => 'Modificar configuraciones'],
            ['name' => 'Ver Historial de Cambios', 'slug' => 'configuration.view.history', 'module' => 'configuration', 'description' => 'Ver historial de configuración'],
            ['name' => 'Limpiar Caché', 'slug' => 'configuration.clear.cache', 'module' => 'configuration', 'description' => 'Limpiar caché del sistema'],

            // ============================================================
            // JOBPROFILE MODULE - Perfiles de Puesto
            // ============================================================
            ['name' => 'Ver Lista de Perfiles', 'slug' => 'jobprofile.view.profiles', 'module' => 'jobprofile', 'description' => 'Ver listado de perfiles de puesto'],
            ['name' => 'Ver Detalle de Perfil', 'slug' => 'jobprofile.view.profile', 'module' => 'jobprofile', 'description' => 'Ver detalle de perfil'],
            ['name' => 'Ver Propios Perfiles', 'slug' => 'jobprofile.view.own', 'module' => 'jobprofile', 'description' => 'Ver perfiles creados por uno mismo'],
            ['name' => 'Solicitar Perfil', 'slug' => 'jobprofile.create.profile', 'module' => 'jobprofile', 'description' => 'Solicitar nuevo perfil de puesto'],
            ['name' => 'Actualizar Perfil Propio', 'slug' => 'jobprofile.update.own', 'module' => 'jobprofile', 'description' => 'Modificar perfil propio en borrador'],
            ['name' => 'Actualizar Cualquier Perfil', 'slug' => 'jobprofile.update.any', 'module' => 'jobprofile', 'description' => 'Modificar cualquier perfil'],
            ['name' => 'Eliminar Perfil', 'slug' => 'jobprofile.delete.profile', 'module' => 'jobprofile', 'description' => 'Eliminar perfil de puesto'],
            ['name' => 'Enviar Perfil para Revisión', 'slug' => 'jobprofile.submit.profile', 'module' => 'jobprofile', 'description' => 'Enviar perfil para revisión'],
            ['name' => 'Revisar Perfil', 'slug' => 'jobprofile.review.profile', 'module' => 'jobprofile', 'description' => 'Revisar perfil solicitado'],
            ['name' => 'Aprobar Perfil', 'slug' => 'jobprofile.approve.profile', 'module' => 'jobprofile', 'description' => 'Aprobar perfil de puesto'],
            ['name' => 'Rechazar Perfil', 'slug' => 'jobprofile.reject.profile', 'module' => 'jobprofile', 'description' => 'Rechazar perfil de puesto'],
            ['name' => 'Solicitar Modificación', 'slug' => 'jobprofile.request.modification', 'module' => 'jobprofile', 'description' => 'Solicitar modificaciones al perfil'],
            ['name' => 'Exportar Perfiles', 'slug' => 'jobprofile.export.profiles', 'module' => 'jobprofile', 'description' => 'Exportar listado de perfiles'],

            // ============================================================
            // JOBPOSTING MODULE - Convocatorias
            // ============================================================
            ['name' => 'Ver Lista de Convocatorias', 'slug' => 'jobposting.view.postings', 'module' => 'jobposting', 'description' => 'Ver todas las convocatorias'],
            ['name' => 'Ver Convocatorias Públicas', 'slug' => 'jobposting.view.public', 'module' => 'jobposting', 'description' => 'Ver convocatorias publicadas'],
            ['name' => 'Ver Detalle de Convocatoria', 'slug' => 'jobposting.view.posting', 'module' => 'jobposting', 'description' => 'Ver detalle de convocatoria'],
            ['name' => 'Crear Convocatoria', 'slug' => 'jobposting.create.posting', 'module' => 'jobposting', 'description' => 'Crear nueva convocatoria'],
            ['name' => 'Actualizar Convocatoria', 'slug' => 'jobposting.update.posting', 'module' => 'jobposting', 'description' => 'Modificar convocatoria'],
            ['name' => 'Eliminar Convocatoria', 'slug' => 'jobposting.delete.posting', 'module' => 'jobposting', 'description' => 'Eliminar convocatoria'],
            ['name' => 'Publicar Convocatoria', 'slug' => 'jobposting.publish.posting', 'module' => 'jobposting', 'description' => 'Publicar convocatoria'],
            ['name' => 'Cancelar Convocatoria', 'slug' => 'jobposting.cancel.posting', 'module' => 'jobposting', 'description' => 'Cancelar convocatoria'],
            ['name' => 'Finalizar Convocatoria', 'slug' => 'jobposting.finalize.posting', 'module' => 'jobposting', 'description' => 'Finalizar proceso de convocatoria'],
            ['name' => 'Gestionar Cronograma', 'slug' => 'jobposting.manage.schedule', 'module' => 'jobposting', 'description' => 'Gestionar cronograma de fases'],
            ['name' => 'Gestionar Fases', 'slug' => 'jobposting.manage.phases', 'module' => 'jobposting', 'description' => 'Gestionar fases del proceso'],
            ['name' => 'Exportar Convocatorias', 'slug' => 'jobposting.export.postings', 'module' => 'jobposting', 'description' => 'Exportar listado de convocatorias'],

            // ============================================================
            // APPLICATION MODULE - Postulaciones
            // ============================================================
            ['name' => 'Ver Todas las Postulaciones', 'slug' => 'application.view.applications', 'module' => 'application', 'description' => 'Ver todas las postulaciones'],
            ['name' => 'Ver Postulación', 'slug' => 'application.view.application', 'module' => 'application', 'description' => 'Ver detalle de postulación'],
            ['name' => 'Ver Propias Postulaciones', 'slug' => 'application.view.own', 'module' => 'application', 'description' => 'Ver sus propias postulaciones'],
            ['name' => 'Crear Postulación', 'slug' => 'application.create.application', 'module' => 'application', 'description' => 'Postular a convocatoria'],
            ['name' => 'Actualizar Postulación Propia', 'slug' => 'application.update.own', 'module' => 'application', 'description' => 'Modificar su postulación'],
            ['name' => 'Actualizar Cualquier Postulación', 'slug' => 'application.update.any', 'module' => 'application', 'description' => 'Modificar cualquier postulación'],
            ['name' => 'Eliminar Postulación', 'slug' => 'application.delete.application', 'module' => 'application', 'description' => 'Eliminar postulación'],
            ['name' => 'Aprobar Postulación', 'slug' => 'application.approve.application', 'module' => 'application', 'description' => 'Aprobar postulación como APTO'],
            ['name' => 'Rechazar Postulación', 'slug' => 'application.reject.application', 'module' => 'application', 'description' => 'Rechazar postulación como NO APTO'],
            ['name' => 'Exportar Postulaciones', 'slug' => 'application.export.applications', 'module' => 'application', 'description' => 'Exportar listado de postulaciones'],

            // ============================================================
            // EVALUATION MODULE - Evaluaciones
            // ============================================================
            ['name' => 'Ver Evaluaciones', 'slug' => 'evaluation.view.evaluations', 'module' => 'evaluation', 'description' => 'Ver listado de evaluaciones'],
            ['name' => 'Ver Evaluación', 'slug' => 'evaluation.view.evaluation', 'module' => 'evaluation', 'description' => 'Ver detalle de evaluación'],
            ['name' => 'Crear Evaluación', 'slug' => 'evaluation.create.evaluation', 'module' => 'evaluation', 'description' => 'Crear nueva evaluación'],
            ['name' => 'Evaluar Postulante', 'slug' => 'evaluation.evaluate.applicant', 'module' => 'evaluation', 'description' => 'Calificar postulante'],
            ['name' => 'Actualizar Evaluación', 'slug' => 'evaluation.update.evaluation', 'module' => 'evaluation', 'description' => 'Modificar evaluación'],
            ['name' => 'Eliminar Evaluación', 'slug' => 'evaluation.delete.evaluation', 'module' => 'evaluation', 'description' => 'Eliminar evaluación'],
            ['name' => 'Aprobar Evaluación', 'slug' => 'evaluation.approve.evaluation', 'module' => 'evaluation', 'description' => 'Aprobar resultados de evaluación'],
            ['name' => 'Ver Resultados', 'slug' => 'evaluation.view.results', 'module' => 'evaluation', 'description' => 'Ver resultados de evaluaciones'],
            ['name' => 'Exportar Evaluaciones', 'slug' => 'evaluation.export.evaluations', 'module' => 'evaluation', 'description' => 'Exportar evaluaciones'],

            // ============================================================
            // JURY MODULE - Jurados
            // ============================================================
            ['name' => 'Ver Jurados', 'slug' => 'jury.view.juries', 'module' => 'jury', 'description' => 'Ver listado de jurados'],
            ['name' => 'Ver Detalle de Jurado', 'slug' => 'jury.view.jury', 'module' => 'jury', 'description' => 'Ver información de jurado'],
            ['name' => 'Crear Jurado', 'slug' => 'jury.create.jury', 'module' => 'jury', 'description' => 'Registrar nuevo jurado'],
            ['name' => 'Actualizar Jurado', 'slug' => 'jury.update.jury', 'module' => 'jury', 'description' => 'Modificar información de jurado'],
            ['name' => 'Eliminar Jurado', 'slug' => 'jury.delete.jury', 'module' => 'jury', 'description' => 'Eliminar jurado'],
            ['name' => 'Asignar Jurado a Convocatoria', 'slug' => 'jury.assign.posting', 'module' => 'jury', 'description' => 'Asignar jurado a proceso'],

            // ============================================================
            // DOCUMENT MODULE - Documentos y Firma Digital
            // ============================================================
            ['name' => 'Ver Documentos', 'slug' => 'document.view.documents', 'module' => 'document', 'description' => 'Ver listado de documentos'],
            ['name' => 'Ver Documento', 'slug' => 'document.view.document', 'module' => 'document', 'description' => 'Ver documento específico'],
            ['name' => 'Subir Documento', 'slug' => 'document.upload.document', 'module' => 'document', 'description' => 'Subir nuevo documento'],
            ['name' => 'Eliminar Documento', 'slug' => 'document.delete.document', 'module' => 'document', 'description' => 'Eliminar documento'],
            ['name' => 'Firmar Documento', 'slug' => 'document.sign.document', 'module' => 'document', 'description' => 'Firmar digitalmente documento'],
            ['name' => 'Verificar Firma', 'slug' => 'document.verify.signature', 'module' => 'document', 'description' => 'Verificar firma digital'],
            ['name' => 'Descargar Documento', 'slug' => 'document.download.document', 'module' => 'document', 'description' => 'Descargar documento'],

            // ============================================================
            // NOTIFICATION MODULE - Notificaciones
            // ============================================================
            ['name' => 'Ver Propias Notificaciones', 'slug' => 'notification.view.own', 'module' => 'notification', 'description' => 'Ver sus notificaciones'],
            ['name' => 'Ver Todas las Notificaciones', 'slug' => 'notification.view.all', 'module' => 'notification', 'description' => 'Ver todas las notificaciones'],
            ['name' => 'Marcar como Leída', 'slug' => 'notification.mark.read', 'module' => 'notification', 'description' => 'Marcar notificación como leída'],
            ['name' => 'Enviar Notificación', 'slug' => 'notification.send.notification', 'module' => 'notification', 'description' => 'Enviar notificaciones'],
            ['name' => 'Enviar Notificación Masiva', 'slug' => 'notification.send.bulk', 'module' => 'notification', 'description' => 'Enviar notificaciones masivas'],

            // ============================================================
            // REPORTING MODULE - Reportes
            // ============================================================
            ['name' => 'Ver Reportes', 'slug' => 'reporting.view.reports', 'module' => 'reporting', 'description' => 'Acceder a módulo de reportes'],
            ['name' => 'Generar Reporte de Usuarios', 'slug' => 'reporting.generate.users', 'module' => 'reporting', 'description' => 'Generar reporte de usuarios'],
            ['name' => 'Generar Reporte de Convocatorias', 'slug' => 'reporting.generate.postings', 'module' => 'reporting', 'description' => 'Generar reporte de convocatorias'],
            ['name' => 'Generar Reporte de Postulaciones', 'slug' => 'reporting.generate.applications', 'module' => 'reporting', 'description' => 'Generar reporte de postulaciones'],
            ['name' => 'Generar Reporte de Evaluaciones', 'slug' => 'reporting.generate.evaluations', 'module' => 'reporting', 'description' => 'Generar reporte de evaluaciones'],
            ['name' => 'Exportar Reporte a Excel', 'slug' => 'reporting.export.excel', 'module' => 'reporting', 'description' => 'Exportar reportes a Excel'],
            ['name' => 'Exportar Reporte a PDF', 'slug' => 'reporting.export.pdf', 'module' => 'reporting', 'description' => 'Exportar reportes a PDF'],
            ['name' => 'Ver Dashboard Analítico', 'slug' => 'reporting.view.dashboard', 'module' => 'reporting', 'description' => 'Ver dashboard con estadísticas'],

            // ============================================================
            // AUDIT MODULE - Auditoría
            // ============================================================
            ['name' => 'Ver Logs de Auditoría', 'slug' => 'audit.view.logs', 'module' => 'audit', 'description' => 'Ver registros de auditoría'],
            ['name' => 'Ver Log Específico', 'slug' => 'audit.view.log', 'module' => 'audit', 'description' => 'Ver detalle de log'],
            ['name' => 'Exportar Logs', 'slug' => 'audit.export.logs', 'module' => 'audit', 'description' => 'Exportar logs de auditoría'],
            ['name' => 'Limpiar Logs Antiguos', 'slug' => 'audit.clean.logs', 'module' => 'audit', 'description' => 'Limpiar logs antiguos'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['slug' => $permissionData['slug']],
                array_merge($permissionData, [
                    'is_active' => true,
                ])
            );
        }
    }
}
