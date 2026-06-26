<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Entities\PasswordResetChallenge;
use Modules\Auth\Services\KbaChallengeService;

class KbaRecoveryController extends Controller
{
    public function __construct(private readonly KbaChallengeService $service)
    {
    }

    public function showStartForm(Request $request)
    {
        $request->session()->put('kba_captcha', $this->generateCaptcha());
        return view('auth::passwords.kba.start');
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8'],
            'captcha' => ['required', 'integer'],
        ], [], [
            'dni' => 'DNI',
            'captcha' => 'verificación',
        ]);

        $expectedCaptcha = $request->session()->pull('kba_captcha');
        if (!$expectedCaptcha || (int) $data['captcha'] !== (int) $expectedCaptcha['answer']) {
            $request->session()->put('kba_captcha', $this->generateCaptcha());
            throw ValidationException::withMessages([
                'captcha' => 'La respuesta de verificación es incorrecta.',
            ]);
        }

        $challenge = $this->service->startChallenge(
            $data['dni'],
            $request->ip(),
            $request->userAgent()
        );

        $request->session()->put('kba_challenge_id', $challenge->id);

        return redirect()->route('password.recover.questions');
    }

    public function showQuestion(Request $request)
    {
        $challenge = $this->resolveChallenge($request);

        if (!$challenge || !$challenge->isPending()) {
            return redirect()->route('password.recover.locked');
        }

        return view('auth::passwords.kba.question', [
            'challenge' => $challenge,
            'question' => $challenge->currentQuestion(),
            'questionNumber' => $challenge->current_index + 1,
            'totalQuestions' => $challenge->totalQuestions(),
        ]);
    }

    public function submitAnswer(Request $request)
    {
        $data = $request->validate([
            'answer' => ['required', 'string', 'max:200'],
        ]);

        $challenge = $this->resolveChallenge($request);
        if (!$challenge) {
            return redirect()->route('password.recover.locked');
        }

        $result = $this->service->submitAnswer($challenge, $data['answer']);

        return match ($result['status']) {
            'verified' => redirect()->route('password.recover.reset', ['token' => $result['reset_token']]),
            'next' => redirect()->route('password.recover.questions'),
            'wrong' => back()->withErrors([
                'answer' => 'Respuesta incorrecta. Te quedan ' . $result['attempts_remaining'] . ' intento(s).',
            ]),
            default => redirect()->route('password.recover.locked'),
        };
    }

    public function showResetForm(Request $request, string $token)
    {
        $challenge = PasswordResetChallenge::where('reset_token', $token)->first();

        if (!$challenge || !$challenge->isResetTokenValid()) {
            return redirect()->route('password.recover.locked');
        }

        return view('auth::passwords.kba.reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $ok = $this->service->resetPassword($data['token'], $data['password']);

        if (!$ok) {
            return redirect()->route('password.recover.locked');
        }

        $request->session()->forget('kba_challenge_id');

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.');
    }

    public function showLocked()
    {
        return view('auth::passwords.kba.locked');
    }

    private function resolveChallenge(Request $request): ?PasswordResetChallenge
    {
        $id = $request->session()->get('kba_challenge_id');
        if (!$id) {
            return null;
        }
        return PasswordResetChallenge::find($id);
    }

    private function generateCaptcha(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        return [
            'question' => "{$a} + {$b}",
            'answer' => $a + $b,
        ];
    }
}
