<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Entities\JobProfileCareer;
use Modules\Application\Entities\AcademicCareer;
use Modules\Application\Entities\AcademicCareerSynonym;
use Modules\Application\Entities\TempJobProfileCareerMapping;

class MapJobProfileCareersCommand extends Command
{
    protected $signature = 'job-profiles:map-careers
                            {--auto-approve=90 : Umbral de confidence para aprobación automática (0-100)}
                            {--dry-run : Ejecutar sin guardar cambios}';

    protected $description = 'Mapea career_field de job_profiles a tabla pivote job_profile_careers';

    protected $stats = [
        'total' => 0,
        'auto_mapped' => 0,
        'pending_review' => 0,
        'no_match' => 0,
        'multiple_careers' => 0,
    ];

    public function handle()
    {
        $autoApproveThreshold = (float) $this->option('auto-approve');
        $isDryRun = $this->option('dry-run');

        $this->info('=== MAPEO DE CARRERAS EN JOB_PROFILES ===');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 MODO DRY-RUN: No se guardarán cambios');
            $this->newLine();
        }

        // Obtener todos los job profiles con career_field
        $jobProfiles = JobProfile::whereNotNull('career_field')
            ->where('career_field', '!=', '')
            ->get();

        $this->stats['total'] = $jobProfiles->count();
        $this->info("Total perfiles a procesar: {$this->stats['total']}");
        $this->newLine();

        $bar = $this->output->createProgressBar($this->stats['total']);
        $bar->start();

        foreach ($jobProfiles as $profile) {
            $this->processJobProfile($profile, $autoApproveThreshold, $isDryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->displaySummary();

        return 0;
    }

    protected function processJobProfile(JobProfile $profile, float $threshold, bool $isDryRun): void
    {
        $careerField = $profile->career_field;

        // Extraer carreras individuales
        $individualCareers = $this->extractIndividualCareers($careerField);

        if (count($individualCareers) > 1) {
            $this->stats['multiple_careers']++;
        }

        foreach ($individualCareers as $careerText) {
            $match = $this->findCareerMatch($careerText);

            if (!$match) {
                $this->stats['no_match']++;
                $this->logNoMatch($profile, $careerText);
                continue;
            }

            $confidence = $match['confidence'];

            if ($confidence >= $threshold) {
                // Mapeo automático
                if (!$isDryRun) {
                    JobProfileCareer::updateOrCreate(
                        [
                            'job_profile_id' => $profile->id,
                            'career_id' => $match['career']->id,
                        ],
                        [
                            'is_primary' => false,
                            'mapping_source' => 'AUTO',
                            'mapped_from_text' => $careerText,
                            'confidence_score' => $confidence,
                        ]
                    );
                }
                $this->stats['auto_mapped']++;
            } else {
                // Requiere revisión manual
                if (!$isDryRun) {
                    TempJobProfileCareerMapping::updateOrCreate(
                        [
                            'job_profile_id' => $profile->id,
                            'career_id' => $match['career']->id,
                            'original_text' => $careerText,
                        ],
                        [
                            'confidence_score' => $confidence,
                            'status' => 'PENDING_REVIEW',
                        ]
                    );
                }
                $this->stats['pending_review']++;
            }
        }
    }

    protected function extractIndividualCareers(string $careerField): array
    {
        $text = $this->normalize($careerField);

        // Eliminar frases genéricas
        $text = preg_replace('/\bO\s+AFINES\b/i', '', $text);
        $text = preg_replace('/\bY\s+AFINES\b/i', '', $text);
        $text = preg_replace('/\bAFINES\b/i', '', $text);
        $text = preg_replace('/\bCARRERA\s+(PROFESIONAL\s+)?DE\b/i', '', $text);
        $text = preg_replace('/\bTITULO\s+(PROFESIONAL\s+)?EN\b/i', '', $text);
        $text = preg_replace('/\bING\.\s+DE\b/i', 'INGENIERIA', $text); // "ING. DE SISTEMAS" -> "INGENIERIA SISTEMAS"
        $text = preg_replace('/\bING\b/i', 'INGENIERIA', $text); // "ING AMBIENTAL" -> "INGENIERIA AMBIENTAL"

        // Separar por comas, "O", "Y" como delimitadores
        $parts = preg_split('/[,\s]+O\s+|[,\s]+Y\s+|,\s*|\/\s*/i', $text);

        // Filtrar vacíos y muy cortos
        $filtered = array_values(array_filter(
            array_map('trim', $parts),
            fn($p) => !empty($p) && strlen($p) > 2 && !in_array(strtolower($p), ['de', 'del', 'la', 'en'])
        ));

        return $filtered;
    }

    protected function findCareerMatch(string $careerText): ?array
    {
        $normalized = $this->normalize($careerText);

        // 1. Buscar exact match en academic_careers.name
        $career = AcademicCareer::whereRaw('LOWER(name) = ?', [$normalized])->first();
        if ($career) {
            return ['career' => $career, 'confidence' => 100.0];
        }

        // 2. Buscar exact match en synonyms
        $synonym = AcademicCareerSynonym::approved()
            ->whereRaw('LOWER(synonym) = ?', [$normalized])
            ->first();
        if ($synonym) {
            return ['career' => $synonym->career, 'confidence' => 95.0];
        }

        // 3. Búsqueda parcial (LIKE)
        $career = AcademicCareer::whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])->first();
        if ($career) {
            $similarity = $this->calculateSimilarity($normalized, strtolower($career->name));
            return ['career' => $career, 'confidence' => $similarity];
        }

        // 4. Búsqueda parcial en sinónimos
        $synonym = AcademicCareerSynonym::approved()
            ->whereRaw('LOWER(synonym) LIKE ?', ["%{$normalized}%"])
            ->first();
        if ($synonym) {
            $similarity = $this->calculateSimilarity($normalized, strtolower($synonym->synonym));
            return ['career' => $synonym->career, 'confidence' => $similarity * 0.9];
        }

        return null;
    }

    protected function normalize(string $text): string
    {
        // Convertir a minúsculas primero
        $text = mb_strtolower($text, 'UTF-8');

        // Remover tildes manualmente (más confiable que iconv)
        $unwanted = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'];
        $wanted   = ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'u'];
        $text = str_replace($unwanted, $wanted, $text);

        // Remover caracteres especiales excepto espacios
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Remover espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    protected function calculateSimilarity(string $str1, string $str2): float
    {
        similar_text($str1, $str2, $percent);
        return round($percent, 2);
    }

    protected function logNoMatch(JobProfile $profile, string $careerText): void
    {
        // Puedes implementar logging aquí si lo necesitas
    }

    protected function displaySummary(): void
    {
        $this->info('=== RESUMEN ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total perfiles procesados', $this->stats['total']],
                ['✓ Mapeados automáticamente', $this->stats['auto_mapped']],
                ['⚠ Requieren revisión manual', $this->stats['pending_review']],
                ['✗ Sin mapeo', $this->stats['no_match']],
                ['ℹ Con múltiples carreras', $this->stats['multiple_careers']],
            ]
        );
    }
}
