<?php

namespace Modules\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para comparar carreras académicas usando NLP
 *
 * Este servicio se comunica con el microservicio Python (career-matcher-nlp)
 * para determinar si una carrera afín declarada por el postulante es
 * similar a las carreras requeridas por el perfil del puesto.
 */
class CareerMatcherService
{
    private string $baseUrl;
    private float $threshold;
    private int $timeout;
    private bool $cacheEnabled;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('services.career_matcher.url', 'http://localhost:8000');
        $this->threshold = config('services.career_matcher.threshold', 0.75);
        $this->timeout = config('services.career_matcher.timeout', 10);
        $this->cacheEnabled = config('services.career_matcher.cache_enabled', true);
        $this->cacheTtl = config('services.career_matcher.cache_ttl', 86400); // 24 horas
    }

    /**
     * Verifica si una carrera afín coincide con las carreras aceptadas
     *
     * @param string $candidateCareer Nombre de la carrera declarada por el postulante
     * @param array $acceptedCareerNames Lista de nombres de carreras aceptadas
     * @param float|null $threshold Umbral de similitud (opcional)
     * @return array Resultado del matching con keys: is_match, score, matched_career, etc.
     */
    public function matchRelatedCareer(
        string $candidateCareer,
        array $acceptedCareerNames,
        ?float $threshold = null
    ): array {
        // Validar inputs
        if (empty($candidateCareer) || empty($acceptedCareerNames)) {
            return $this->createResult(
                isMatch: false,
                score: 0,
                reason: 'Datos de entrada vacíos'
            );
        }

        // Generar cache key
        $cacheKey = $this->generateCacheKey($candidateCareer, $acceptedCareerNames, $threshold);

        // Intentar obtener de cache
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $cached['from_cache'] = true;
            return $cached;
        }

        // Llamar al servicio NLP
        $result = $this->callNlpService($candidateCareer, $acceptedCareerNames, $threshold);

        // Guardar en cache si está habilitado y el resultado es válido
        if ($this->cacheEnabled && !isset($result['requires_manual_review'])) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    /**
     * Procesa múltiples carreras en batch
     *
     * @param array $candidateCareers Lista de carreras a evaluar
     * @param array $acceptedCareerNames Lista de carreras aceptadas
     * @param float|null $threshold Umbral de similitud
     * @return array Lista de resultados para cada carrera
     */
    public function batchMatch(
        array $candidateCareers,
        array $acceptedCareerNames,
        ?float $threshold = null
    ): array {
        if (empty($candidateCareers) || empty($acceptedCareerNames)) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout * 2) // Más tiempo para batch
                ->post("{$this->baseUrl}/api/v1/batch-match", [
                    'candidate_careers' => $candidateCareers,
                    'accepted_careers' => $acceptedCareerNames,
                    'threshold' => $threshold ?? $this->threshold,
                ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }

            Log::warning('Career matcher batch service returned error', [
                'status' => $response->status(),
            ]);

            // Fallback: procesar individualmente
            return array_map(
                fn($career) => $this->matchRelatedCareer($career, $acceptedCareerNames, $threshold),
                $candidateCareers
            );

        } catch (\Exception $e) {
            Log::error('Career matcher batch service unavailable', [
                'error' => $e->getMessage(),
            ]);

            return array_map(
                fn($career) => $this->fallbackResult("Servicio no disponible"),
                $candidateCareers
            );
        }
    }

    /**
     * Verifica si el servicio NLP está disponible
     *
     * @return bool
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/health");
            return $response->successful() && ($response->json()['status'] ?? '') === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si el servicio está listo para procesar requests
     *
     * @return bool
     */
    public function isServiceReady(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/ready");
            return $response->successful() && ($response->json()['ready'] ?? false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Llama al servicio NLP para hacer el matching
     */
    private function callNlpService(
        string $candidateCareer,
        array $acceptedCareerNames,
        ?float $threshold
    ): array {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/match-career", [
                    'candidate_career' => $candidateCareer,
                    'accepted_careers' => $acceptedCareerNames,
                    'threshold' => $threshold ?? $this->threshold,
                    'include_all_scores' => true,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->createResult(
                    isMatch: $data['is_match'] ?? false,
                    score: $data['score'] ?? 0,
                    matchedCareer: $data['matched_career'] ?? null,
                    matchType: $data['match_type'] ?? null,
                    reason: $data['reason'] ?? null,
                    allScores: $data['all_scores'] ?? null
                );
            }

            Log::warning('Career matcher service returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'candidate' => $candidateCareer,
            ]);

            return $this->fallbackResult("Error del servicio: HTTP {$response->status()}");

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Career matcher service connection failed', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl,
            ]);

            return $this->fallbackResult('Servicio NLP no disponible (conexión fallida)');

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Career matcher service request failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResult('Error en la solicitud al servicio NLP');

        } catch (\Exception $e) {
            Log::error('Career matcher service unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->fallbackResult('Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Crea un resultado estructurado
     */
    private function createResult(
        bool $isMatch,
        float $score,
        ?string $matchedCareer = null,
        ?string $matchType = null,
        ?string $reason = null,
        ?array $allScores = null,
        bool $requiresManualReview = false
    ): array {
        $result = [
            'is_match' => $isMatch,
            'score' => round($score, 4),
            'matched_career' => $matchedCareer,
            'match_type' => $matchType,
            'reason' => $reason,
            'threshold_used' => $this->threshold,
            'requires_manual_review' => $requiresManualReview,
        ];

        if ($allScores !== null) {
            $result['all_scores'] = $allScores;
        }

        return $result;
    }

    /**
     * Resultado fallback cuando el servicio no está disponible
     *
     * Marca la evaluación como "pendiente de revisión manual"
     * para que un evaluador humano revise el caso.
     */
    private function fallbackResult(string $errorMessage): array
    {
        return $this->createResult(
            isMatch: false,
            score: 0,
            reason: $errorMessage,
            requiresManualReview: true
        );
    }

    /**
     * Genera cache key única para la combinación de inputs
     */
    private function generateCacheKey(
        string $candidateCareer,
        array $acceptedCareerNames,
        ?float $threshold
    ): string {
        $data = [
            'candidate' => mb_strtolower(trim($candidateCareer)),
            'accepted' => array_map(fn($c) => mb_strtolower(trim($c)), $acceptedCareerNames),
            'threshold' => $threshold ?? $this->threshold,
        ];

        sort($data['accepted']); // Ordenar para consistencia

        return 'career_match:' . md5(json_encode($data));
    }

    /**
     * Limpia el cache de matching de carreras
     *
     * @param string|null $candidateCareer Si se especifica, limpia solo ese candidato
     * @return bool
     */
    public function clearCache(?string $candidateCareer = null): bool
    {
        if ($candidateCareer === null) {
            // Limpiar todo el cache de career_match
            // Nota: Esto requiere un driver de cache que soporte tags o pattern delete
            // Con Redis: Cache::getRedis()->del(Cache::getRedis()->keys('career_match:*'));
            return true;
        }

        // Limpiar entrada específica requiere conocer las accepted_careers
        // Por ahora, no implementamos limpieza selectiva
        return false;
    }
}
