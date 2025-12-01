<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Entities\Role;
use Modules\Auth\Entities\Permission;

class RolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Matriz de permisos por rol
        $rolePermissions = $this->getRolePermissionsMatrix();

        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();

            if (!$role) {
                $this->command->warn("Rol no encontrado: {$roleSlug}");
                continue;
            }

            // Si el rol tiene '*' significa todos los permisos
            if (in_array('*', $permissionSlugs)) {
                $permissions = Permission::where('is_active', true)->pluck('id');
            } else {
                $permissions = Permission::whereIn('slug', $permissionSlugs)
                    ->where('is_active', true)
                    ->pluck('id');
            }

            // Sync permisos con el rol
            $role->permissions()->sync($permissions);

            $this->command->info("Permisos asignados al rol: {$role->name} ({$permissions->count()} permisos)");
        }
    }

    /**
     * Matriz de permisos por rol
     */
    protected function getRolePermissionsMatrix(): array
    {
        return [
            // ============================================================
            // SUPER ADMIN - Acceso total
            // ============================================================
            'super-admin' => [
                '*', // Todos los permisos
            ],

            // ============================================================
            // ADMIN RRHH - Gestión completa de convocatorias
            // ============================================================
            'admin-rrhh' => [
                // Auth
                'auth.view.roles',
                'auth.view.role',
                'auth.view.permissions',
                'auth.assign.role',
                'auth.revoke.role',
                'auth.view.sessions',

                // User
                'user.view.users',
                'user.view.user',
                'user.view.own',
                'user.create.user',
                'user.update.user',
                'user.update.own',
                'user.toggle.status',
                'user.reset.password',
                'user.manage.preferences',
                'user.export.users',

                // Organization
                'organization.view.units',
                'organization.view.unit',
                'organization.create.unit',
                'organization.update.unit',
                'organization.delete.unit',
                'organization.move.unit',
                'organization.export.structure',

                // Configuration
                'configuration.view.configs',
                'configuration.update.config',
                'configuration.view.history',

                // JobProfile
                'jobprofile.view.profiles',
                'jobprofile.view.profile',
                'jobprofile.view.own',
                'jobprofile.create.profile',
                'jobprofile.update.own',
                'jobprofile.update.any',
                'jobprofile.delete.profile',
                'jobprofile.submit.profile',
                'jobprofile.review.profile',
                'jobprofile.approve.profile',
                'jobprofile.reject.profile',
                'jobprofile.request.modification',
                'jobprofile.export.profiles',

                // JobPosting
                'jobposting.view.postings',
                'jobposting.view.posting',
                'jobposting.create.posting',
                'jobposting.update.posting',
                'jobposting.delete.posting',
                'jobposting.publish.posting',
                'jobposting.cancel.posting',
                'jobposting.finalize.posting',
                'jobposting.manage.schedule',
                'jobposting.manage.phases',
                'jobposting.export.postings',

                // Application
                'application.view.applications',
                'application.view.application',
                'application.update.any',
                'application.approve.application',
                'application.reject.application',
                'application.export.applications',

                // Evaluation
                'evaluation.view.evaluations',
                'evaluation.view.evaluation',
                'evaluation.approve.evaluation',
                'evaluation.view.results',
                'evaluation.export.evaluations',

                // Jury
                'jury.view.juries',
                'jury.view.jury',
                'jury.create.jury',
                'jury.update.jury',
                'jury.delete.jury',
                'jury.assign.posting',

                // Document
                'document.view.documents',
                'document.view.document',
                'document.download.document',
                'document.verify.signature',

                // Notification
                'notification.view.own',
                'notification.view.all',
                'notification.mark.read',
                'notification.send.notification',
                'notification.send.bulk',

                // Reporting
                'reporting.view.reports',
                'reporting.generate.users',
                'reporting.generate.postings',
                'reporting.generate.applications',
                'reporting.generate.evaluations',
                'reporting.export.excel',
                'reporting.export.pdf',
                'reporting.view.dashboard',

                // Audit
                'audit.view.logs',
                'audit.view.log',
                'audit.export.logs',
            ],

            // ============================================================
            // AREA USER - Solicita perfiles de puesto
            // ============================================================
            'area-user' => [
                // User
                'user.view.own',
                'user.update.own',

                // Organization
                'organization.view.units',
                'organization.view.unit',

                // JobProfile
                'jobprofile.view.profiles',
                'jobprofile.view.profile',
                'jobprofile.view.own',
                'jobprofile.create.profile',
                'jobprofile.update.own',
                'jobprofile.submit.profile',

                // JobPosting
                'jobposting.view.postings',
                'jobposting.view.posting',

                // Notification
                'notification.view.own',
                'notification.mark.read',
            ],

            // ============================================================
            // RRHH REVIEWER - Revisa y aprueba perfiles
            // ============================================================
            'rrhh-reviewer' => [
                // User
                'user.view.own',
                'user.update.own',

                // Organization
                'organization.view.units',
                'organization.view.unit',

                // JobProfile
                'jobprofile.view.profiles',
                'jobprofile.view.profile',
                'jobprofile.review.profile',
                'jobprofile.approve.profile',
                'jobprofile.reject.profile',
                'jobprofile.request.modification',
                'jobprofile.export.profiles',

                // JobPosting
                'jobposting.view.postings',
                'jobposting.view.posting',

                // Notification
                'notification.view.own',
                'notification.mark.read',
                'notification.send.notification',

                // Reporting
                'reporting.view.reports',
                'reporting.generate.postings',
                'reporting.export.excel',
                'reporting.export.pdf',
            ],

            // ============================================================
            // JURY - Evalúa postulaciones
            // ============================================================
            'jury' => [
                // User
                'user.view.own',
                'user.update.own',

                // JobPosting
                'jobposting.view.postings',
                'jobposting.view.posting',

                // Application
                'application.view.applications',
                'application.view.application',

                // Evaluation
                'evaluation.view.evaluations',
                'evaluation.view.evaluation',
                'evaluation.create.evaluation',
                'evaluation.evaluate.applicant',
                'evaluation.update.evaluation',
                'evaluation.view.results',

                // Document
                'document.view.documents',
                'document.view.document',
                'document.download.document',

                // Notification
                'notification.view.own',
                'notification.mark.read',
            ],

            // ============================================================
            // APPLICANT - Postula a convocatorias
            // ============================================================
            'applicant' => [
                // User
                'user.view.own',
                'user.update.own',

                // JobPosting
                'jobposting.view.public',
                'jobposting.view.posting',

                // Application
                'application.view.own',
                'application.view.application',
                'application.create.application',
                'application.update.own',

                // Document
                'document.view.documents',
                'document.view.document',
                'document.upload.document',
                'document.download.document',

                // Notification
                'notification.view.own',
                'notification.mark.read',
            ],

            // ============================================================
            // VIEWER - Solo visualización
            // ============================================================
            'viewer' => [
                // User
                'user.view.own',

                // Organization
                'organization.view.units',
                'organization.view.unit',

                // JobProfile
                'jobprofile.view.profiles',
                'jobprofile.view.profile',

                // JobPosting
                'jobposting.view.postings',
                'jobposting.view.posting',

                // Application
                'application.view.applications',
                'application.view.application',

                // Evaluation
                'evaluation.view.evaluations',
                'evaluation.view.evaluation',
                'evaluation.view.results',

                // Notification
                'notification.view.own',
                'notification.mark.read',

                // Reporting
                'reporting.view.reports',
                'reporting.view.dashboard',
            ],
        ];
    }
}
