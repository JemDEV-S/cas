<?php

namespace Modules\Auth\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Entities\PasswordResetChallenge;
use Modules\User\Entities\User;

class KbaChallengeService
{
    private const REQUIRED_QUESTIONS = 3;
    private const CHALLENGE_TTL_MINUTES = 10;
    private const RATE_LIMIT_ATTEMPTS = 3;
    private const RATE_LIMIT_DECAY_MINUTES = 60;

    public function startChallenge(string $dni, string $ip, ?string $userAgent): PasswordResetChallenge
    {
        $this->enforceRateLimit($dni, $ip);

        $user = User::with(['profile', 'roles'])->where('dni', $dni)->first();

        if (!$user || !$user->hasRole('applicant')) {
            RateLimiter::hit($this->rateLimitKey($dni, $ip), self::RATE_LIMIT_DECAY_MINUTES * 60);
            throw ValidationException::withMessages([
                'dni' => 'No podemos procesar tu solicitud con los datos proporcionados.',
            ]);
        }

        $pool = $this->buildQuestionPool($user);

        if (count($pool) < self::REQUIRED_QUESTIONS) {
            throw ValidationException::withMessages([
                'dni' => 'No es posible recuperar tu cuenta por este medio. Comunícate con la Oficina de Tecnologías de la Información.',
            ]);
        }

        shuffle($pool);
        $selected = array_slice($pool, 0, self::REQUIRED_QUESTIONS);

        $questionsForStorage = array_map(function (array $q) {
            return [
                'prompt' => $q['prompt'],
                'answer_hash' => Hash::make($this->normalize($q['answer'])),
                'answered' => false,
            ];
        }, $selected);

        return PasswordResetChallenge::create([
            'user_id' => $user->id,
            'dni' => $dni,
            'questions' => $questionsForStorage,
            'current_index' => 0,
            'attempts_used' => 0,
            'max_attempts' => 2,
            'status' => 'pending',
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
            'expires_at' => now()->addMinutes(self::CHALLENGE_TTL_MINUTES),
        ]);
    }

    public function submitAnswer(PasswordResetChallenge $challenge, string $answer): array
    {
        if (!$challenge->isPending()) {
            return ['status' => 'invalid'];
        }

        $question = $challenge->currentQuestion();
        if (!$question) {
            return ['status' => 'invalid'];
        }

        $isCorrect = Hash::check($this->normalize($answer), $question['answer_hash']);

        if ($isCorrect) {
            $questions = $challenge->questions;
            $questions[$challenge->current_index]['answered'] = true;
            $nextIndex = $challenge->current_index + 1;

            if ($nextIndex >= $challenge->totalQuestions()) {
                $challenge->update(['questions' => $questions, 'current_index' => $nextIndex]);
                $token = $challenge->markVerified();
                return ['status' => 'verified', 'reset_token' => $token];
            }

            $challenge->update([
                'questions' => $questions,
                'current_index' => $nextIndex,
            ]);
            return ['status' => 'next', 'attempts_remaining' => $challenge->attemptsRemaining()];
        }

        $challenge->increment('attempts_used');
        $challenge->refresh();

        if ($challenge->attemptsRemaining() <= 0) {
            $challenge->markFailed();
            return ['status' => 'failed'];
        }

        return ['status' => 'wrong', 'attempts_remaining' => $challenge->attemptsRemaining()];
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $challenge = PasswordResetChallenge::where('reset_token', $token)->first();

        if (!$challenge || !$challenge->isResetTokenValid()) {
            return false;
        }

        $user = $challenge->user;
        if (!$user) {
            return false;
        }

        $user->forceFill(['password' => Hash::make($newPassword)])->save();

        $challenge->update([
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return true;
    }

    private function buildQuestionPool(User $user): array
    {
        $pool = [];

        $birthDate = $user->birth_date ?? $user->profile?->birth_date;
        if ($birthDate) {
            $birth = Carbon::parse($birthDate);
            $pool[] = [
                'prompt' => '¿En qué año naciste? (ejemplo: 1990)',
                'answer' => $birth->format('Y'),
            ];
            $pool[] = [
                'prompt' => '¿En qué mes naciste? (escribe el nombre, por ejemplo: enero)',
                'answer' => $this->monthName((int) $birth->format('m')),
            ];
            $pool[] = [
                'prompt' => '¿Qué día del mes naciste? (solo el número, por ejemplo: 15)',
                'answer' => $birth->format('j'),
            ];
        }

        $department = $user->department ?? $user->profile?->department;
        if ($department) {
            $pool[] = [
                'prompt' => '¿En qué departamento resides?',
                'answer' => $department,
            ];
        }

        $province = $user->province ?? $user->profile?->province;
        if ($province) {
            $pool[] = [
                'prompt' => '¿En qué provincia resides?',
                'answer' => $province,
            ];
        }

        $district = $user->district ?? $user->profile?->district;
        if ($district) {
            $pool[] = [
                'prompt' => '¿En qué distrito resides?',
                'answer' => $district,
            ];
        }

        if ($user->first_name) {
            $firstName = trim(explode(' ', trim($user->first_name))[0]);
            if ($firstName !== '') {
                $pool[] = [
                    'prompt' => '¿Cuál es tu primer nombre?',
                    'answer' => $firstName,
                ];
            }
        }

        if ($user->last_name) {
            $parts = preg_split('/\s+/', trim($user->last_name));
            if (!empty($parts[0])) {
                $pool[] = [
                    'prompt' => '¿Cuál es tu primer apellido (paterno)?',
                    'answer' => $parts[0],
                ];
            }
            if (!empty($parts[1])) {
                $pool[] = [
                    'prompt' => '¿Cuál es tu segundo apellido (materno)?',
                    'answer' => $parts[1],
                ];
            }
        }

        if ($user->email) {
            $pool[] = [
                'prompt' => '¿Cuál es el correo electrónico que registraste en el sistema?',
                'answer' => $user->email,
            ];
        }

        if ($user->phone) {
            $pool[] = [
                'prompt' => '¿Cuál es el número de teléfono que registraste en el sistema?',
                'answer' => preg_replace('/\D+/', '', $user->phone),
            ];
        }

        return $this->dedupePool($pool);
    }

    private function dedupePool(array $pool): array
    {
        $seen = [];
        $result = [];
        foreach ($pool as $item) {
            $normAnswer = $this->normalize((string) $item['answer']);
            if ($normAnswer === '') {
                continue;
            }
            $key = $item['prompt'] . '|' . $normAnswer;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $item;
        }
        return $result;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            'ü' => 'u',
        ];
        $value = strtr($value, $replacements);

        // Si la respuesta es puramente numérica (con separadores típicos de teléfono),
        // quedarse solo con los dígitos para tolerar formatos como "+51 999 888 777".
        $digitsOnly = preg_replace('/[\s\-\(\)\+\.]+/', '', $value);
        if ($digitsOnly !== '' && ctype_digit($digitsOnly)) {
            return $digitsOnly;
        }

        return preg_replace('/\s+/u', ' ', $value);
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ][$month] ?? '';
    }

    private function enforceRateLimit(string $dni, string $ip): void
    {
        $key = $this->rateLimitKey($dni, $ip);
        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'dni' => 'Has alcanzado el número máximo de intentos. Intenta de nuevo en ' . ceil($seconds / 60) . ' minutos.',
            ]);
        }
        RateLimiter::hit($key, self::RATE_LIMIT_DECAY_MINUTES * 60);
    }

    private function rateLimitKey(string $dni, string $ip): string
    {
        return 'kba-start:' . $dni . '|' . $ip;
    }
}
