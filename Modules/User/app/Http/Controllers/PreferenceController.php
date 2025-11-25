<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\Services\PreferenceService;

class PreferenceController extends Controller
{
    public function __construct(
        protected PreferenceService $preferenceService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show the form for editing preferences.
     */
    public function edit()
    {
        $user = Auth::user();
        $user->load('preference');

        return view('user::preferences.edit', compact('user'));
    }

    /**
     * Update the user's preferences.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'language' => ['nullable', 'string', 'in:es,en'],
            'theme' => ['nullable', 'string', 'in:light,dark,auto'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'items_per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        try {
            $this->preferenceService->updateOrCreate($user, $validated);

            return redirect()
                ->route('profile.preferences')
                ->with('success', 'Preferencias actualizadas exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar preferencias: ' . $e->getMessage());
        }
    }
}
