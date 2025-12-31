<?php

declare(strict_types=1);

namespace Modules\JobProfile\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\JobPosting\Events\JobPostingPublished;
use Modules\JobProfile\Enums\JobProfileStatusEnum;

class ActivateJobProfiles
{
    public function handle(JobPostingPublished $event): void
    {
        $jobPosting = $event->jobPosting;

        // Obtener perfiles aprobados
        $approvedProfiles = $jobPosting->jobProfiles()
            ->where('status', JobProfileStatusEnum::APPROVED->value)
            ->get();

        if ($approvedProfiles->isEmpty()) {
            return;
        }

        // Activar cada perfil
        $updated = 0;
        foreach ($approvedProfiles as $profile) {
            $profile->status = JobProfileStatusEnum::ACTIVE->value;
            $profile->save();
            $updated++;
        }

        Log::info('Perfiles activados después de publicación', [
            'job_posting_id' => $jobPosting->id,
            'profiles_activated' => $updated,
            'profile_codes' => $approvedProfiles->pluck('code')->toArray(),
        ]);
    }
}
