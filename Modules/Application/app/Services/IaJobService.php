<?php

namespace Modules\Application\Services;

use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Entities\IaJob;

class IaJobService
{
    /**
     * Crear un job de evaluación IA para una postulación.
     *
     * Compara directamente la carrera del postulante contra el career_field
     * del JobProfile usando el LLM local (Ollama).
     */
    public function createCareerEvaluationJob(Application $application): ?IaJob
    {
        $jobProfile = $application->jobProfile;

        if (!$jobProfile) {
            Log::channel('ia')->warning("No se pudo crear job IA: application {$application->id} sin job_profile");
            return null;
        }

        // career_field del perfil es el texto que describe las carreras requeridas
        $careerField = $jobProfile->career_field;

        if (empty($careerField)) {
            Log::channel('ia')->warning("No se pudo crear job IA: perfil {$jobProfile->id} sin career_field");
            return null;
        }

        // Obtener carrera del postulante
        $academic = $application->academics->first();
        if (!$academic) {
            Log::channel('ia')->warning("No se pudo crear job IA: application {$application->id} sin formación académica");
            return null;
        }

        $applicantCareer = $academic->career
            ? $academic->career->name
            : ($academic->related_career_name ?? $academic->career_field ?? 'No especificada');

        // Evitar duplicados: no crear si ya existe un job pendiente/procesando
        $existing = IaJob::where('application_id', $application->id)
            ->whereIn('status', ['pendiente', 'procesando'])
            ->first();

        if ($existing) {
            Log::channel('ia')->info("Job IA ya existe para application {$application->id}: {$existing->id}");
            return $existing;
        }

        $job = IaJob::create([
            'application_id' => $application->id,
            'job_profile_id' => $jobProfile->id,
            'applicant_career' => $applicantCareer,
            'required_careers' => $careerField,
            'applicant_degree_type' => $academic->degree_type,
            'status' => 'pendiente',
        ]);

        Log::channel('ia')->info("Job IA creado: {$job->id} | Postulante: {$applicantCareer} | career_field: {$careerField}");

        return $job;
    }

    /**
     * Obtener el resultado de evaluación IA para una postulación.
     */
    public function getResult(Application $application): ?IaJob
    {
        return IaJob::where('application_id', $application->id)
            ->where('status', 'completado')
            ->latest()
            ->first();
    }
}
