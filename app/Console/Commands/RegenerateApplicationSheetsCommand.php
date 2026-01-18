<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Document\Entities\DocumentTemplate;
use Modules\Document\Services\DocumentService;

class RegenerateApplicationSheetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:regenerate-sheets {--all : Regenerar todos los documentos, incluso si ya existen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenera las fichas de postulación en PDF para todas las postulaciones enviadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando regeneración de fichas de postulación...');
        $this->newLine();

        // Obtener el template
        $template = DocumentTemplate::where('code', 'TPL_APPLICATION_SHEET')
            ->where('status', 'active')
            ->first();

        if (!$template) {
            $this->error('Template TPL_APPLICATION_SHEET no encontrado o inactivo.');
            return Command::FAILURE;
        }

        $this->info("✓ Template encontrado: {$template->name}");
        $this->newLine();

        // Obtener postulaciones en estado SUBMITTED
        $query = Application::where('status', ApplicationStatus::SUBMITTED)
            ->with([
                'applicant',
                'jobProfile.jobPosting',  // ← ACTUALIZADO: relación directa
                'assignedVacancy',        // ← ACTUALIZADO: vacante asignada si existe
                'academics.career',
                'experiences',
                'trainings',
                'knowledge',
                'professionalRegistrations',
                'specialConditions',
                'generatedDocuments'
            ]);

        $applications = $query->get();

        if ($applications->isEmpty()) {
            $this->warn('No se encontraron postulaciones en estado SUBMITTED.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$applications->count()} postulaciones para procesar.");
        $this->newLine();

        $documentService = app(DocumentService::class);
        $bar = $this->output->createProgressBar($applications->count());
        $bar->start();

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($applications as $application) {
            try {
                // Verificar si ya tiene un documento generado
                $existingDoc = $application->generatedDocuments()
                    ->where('document_template_id', $template->id)
                    ->first();

                if ($existingDoc && !$this->option('all')) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Si ya existe y se especificó --all, eliminar el anterior
                if ($existingDoc && $this->option('all')) {
                    $existingDoc->delete();
                }

                // Preparar datos
                $data = $this->prepareApplicationSheetData($application);

                // Generar documento
                $document = $documentService->generateFromTemplate(
                    $template,
                    $application,
                    $data,
                    $application->applicant_id
                );

                $generated++;

            } catch (\Exception $e) {
                $errors++;
                \Log::error('Error al regenerar ficha de postulación', [
                    'application_id' => $application->id,
                    'application_code' => $application->code,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  RESUMEN DE REGENERACIÓN');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  Total procesadas:  {$applications->count()}");
        $this->info("  ✓ Generadas:       {$generated}");
        if ($skipped > 0) {
            $this->comment("  ⊘ Omitidas:        {$skipped} (ya existían)");
        }
        if ($errors > 0) {
            $this->error("  ✗ Errores:         {$errors}");
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($skipped > 0 && !$this->option('all')) {
            $this->comment('💡 Usa la opción --all para regenerar todos los documentos incluso si ya existen.');
        }

        return Command::SUCCESS;
    }

    /**
     * Preparar datos de la postulación para el template
     */
    private function prepareApplicationSheetData(Application $application): array
    {
        // ← ACTUALIZADO: usar relación directa
        $jobProfile = $application->jobProfile;
        $jobPosting = $jobProfile->jobPosting;

        // Calcular edad
        $age = null;
        if ($application->birth_date) {
            $birthDate = \Carbon\Carbon::parse($application->birth_date);
            $age = $birthDate->age;
        }

        return [
            'title' => 'Ficha de Postulación - ' . $application->code,

            // Datos de la postulación
            'application_code' => $application->code,
            'application_date' => $application->application_date?->format('d/m/Y'),

            // Datos de la convocatoria y perfil
            'job_posting_title' => $jobPosting->title ?? 'N/A',
            'job_posting_code' => $jobPosting->code ?? 'N/A',
            'job_profile_name' => $jobProfile->profile_name ?? 'N/A',
            'profile_code' => $jobProfile->code ?? 'N/A',
            // ← REMOVIDO: vacancy_code (se asigna después de la evaluación)

            // Datos personales
            'full_name' => $application->full_name,
            'dni' => $application->dni,
            'birth_date' => $application->birth_date?->format('d/m/Y'),
            'age' => $age,
            'email' => $application->email,
            'phone' => $application->phone,
            'mobile_phone' => $application->mobile_phone,
            'address' => $application->address,

            // Formación académica
            'academics' => $application->academics->map(function ($academic) {
                // Convertir el degree_type al label del enum si es posible
                $degreeTypeLabel = $academic->degree_type;
                try {
                    if ($academic->degree_type) {
                        $enum = \Modules\JobProfile\Enums\EducationLevelEnum::from($academic->degree_type);
                        $degreeTypeLabel = $enum->label();
                    }
                } catch (\ValueError $e) {
                    // Si el valor no es válido en el enum, usar el valor original
                    $degreeTypeLabel = $academic->degree_type;
                }

                return [
                    'institution_name' => $academic->institution_name,
                    'degree_type' => $academic->degree_type,
                    'degree_type_label' => $degreeTypeLabel,
                    'career_field' => $academic->career?->name ?? $academic->career_field,
                    'degree_title' => $academic->degree_title,
                    'issue_date' => $academic->issue_date?->format('Y'),
                    'is_related_career' => $academic->is_related_career,
                    'related_career_name' => $academic->related_career_name,
                ];
            })->toArray(),

                        // Experiencia laboral general (incluye TODAS las experiencias)
            'general_experiences' => $application->experiences->map(function ($experience) {
                return [
                    'organization' => $experience->organization,
                    'position' => $experience->position,
                    'start_date' => $experience->start_date?->format('d/m/Y'),
                    'end_date' => $experience->end_date?->format('d/m/Y'),
                    'duration_days' => $experience->duration_days,
                    'is_specific' => $experience->is_specific,
                    'is_public_sector' => $experience->is_public_sector,
                ];
            })->toArray(),

            // Experiencia laboral específica (solo las específicas)
            'specific_experiences' => $application->experiences->where('is_specific', true)->map(function ($experience) {
                return [
                    'organization' => $experience->organization,
                    'position' => $experience->position,
                    'start_date' => $experience->start_date?->format('d/m/Y'),
                    'end_date' => $experience->end_date?->format('d/m/Y'),
                    'duration_days' => $experience->duration_days,
                    'is_specific' => $experience->is_specific,
                    'is_public_sector' => $experience->is_public_sector,
                ];
            })->values()->toArray(),

            // Calcular totales de experiencia
            'total_general_experience' => $this->calculateExperienceSummary(
                $application->experiences // TODAS las experiencias
            ),
            'total_specific_experience' => $this->calculateExperienceSummary(
                $application->experiences->where('is_specific', true)
            ),

            // Capacitaciones
            'trainings' => $application->trainings->map(function ($training) {
                return [
                    'institution' => $training->institution,
                    'course_name' => $training->course_name,
                    'academic_hours' => $training->academic_hours,
                    'start_date' => $training->start_date?->format('Y-m-d'),
                ];
            })->toArray(),

            // Conocimientos
            'knowledge' => $application->knowledge->map(function ($k) {
                return [
                    'knowledge_name' => $k->knowledge_name,
                    'proficiency_level' => $k->proficiency_level,
                ];
            })->toArray(),

            // Registros profesionales
            'professional_registrations' => $application->professionalRegistrations->map(function ($reg) {
                return [
                    'type' => $reg->registration_type,
                    'number' => $reg->registration_number,
                    'institution' => $reg->issuing_entity,
                    'category' => null,
                    'expiry_date' => $reg->expiry_date?->format('d/m/Y'),
                ];
            })->toArray(),

            // Condiciones especiales
            'special_conditions' => $application->specialConditions->map(function ($condition) {
                return [
                    'type' => $condition->type,
                    'bonus_percentage' => $condition->bonus_percentage,
                ];
            })->toArray(),

            // Información adicional
            'ip_address' => $application->ip_address,
            'generation_date' => now()->format('d/m/Y'),
            'generation_time' => now()->format('H:i:s'),
        ];
    }

    /**
     * Calcular resumen de experiencia (total en años, meses y días)
     */
    private function calculateExperienceSummary($experiences): array
    {
        $totalDays = $experiences->sum('duration_days');

        if ($totalDays <= 0) {
            return [
                'total_days' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => '0 días',
            ];
        }

        $years = floor($totalDays / 365);
        $months = floor(($totalDays % 365) / 30);
        $days = $totalDays % 30;

        $parts = [];
        if ($years > 0) $parts[] = "{$years} año" . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = "{$months} mes" . ($months > 1 ? 'es' : '');
        if ($days > 0) $parts[] = "{$days} día" . ($days > 1 ? 's' : '');

        return [
            'total_days' => $totalDays,
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'formatted' => implode(', ', $parts) ?: '0 días',
        ];
    }
}
