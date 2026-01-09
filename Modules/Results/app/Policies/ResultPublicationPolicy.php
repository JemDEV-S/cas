<?php

namespace Modules\Results\Policies;

use App\Models\User;
use Modules\Results\Entities\ResultPublication;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResultPublicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any result publications.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('results.view') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can view the result publication.
     */
    public function view(User $user, ResultPublication $publication): bool
    {
        return $user->hasPermission('results.view') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can publish Phase 4 results.
     */
    public function publishPhase4(User $user): bool
    {
        return $user->hasPermission('results.publish.phase4') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can publish Phase 7 results.
     */
    public function publishPhase7(User $user): bool
    {
        return $user->hasPermission('results.publish.phase7') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can publish Phase 9 results.
     */
    public function publishPhase9(User $user): bool
    {
        return $user->hasPermission('results.publish.phase9') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can unpublish results.
     */
    public function unpublish(User $user, ResultPublication $publication): bool
    {
        return ($user->hasPermission('results.unpublish') ||
                $user->hasPermission('results.manage.all') ||
                $user->isAdmin()) &&
               $publication->canBeUnpublished();
    }

    /**
     * Determine if the user can republish results.
     */
    public function republish(User $user, ResultPublication $publication): bool
    {
        return ($user->hasPermission('results.republish') ||
                $user->hasPermission('results.manage.all') ||
                $user->isAdmin()) &&
               $publication->canBeRepublished();
    }

    /**
     * Determine if the user can download result documents.
     */
    public function download(User $user): bool
    {
        return $user->hasPermission('results.download') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can generate Excel exports.
     */
    public function generateExcel(User $user): bool
    {
        return $user->hasPermission('results.export.excel') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can configure signers.
     */
    public function configureSigners(User $user): bool
    {
        return $user->hasPermission('results.configure.signers') ||
               $user->hasPermission('results.manage.all') ||
               $user->isAdmin();
    }
}
