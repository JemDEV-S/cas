<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return view('applicantportal::profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date'],
            'gender'     => ['nullable', 'string', 'in:MASCULINO,FEMENINO'],
            'district'   => ['nullable', 'string', 'max:255'],
            'province'   => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $textFields = ['first_name', 'last_name', 'address', 'district', 'province', 'department'];
        foreach ($textFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = Str::upper($validated[$field]);
            }
        }

        $user = Auth::user();
        $user->update($validated);

        return redirect()
            ->route('applicant.profile.show')
            ->with('success', 'Tu perfil ha sido actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()
            ->route('applicant.profile.show')
            ->with('success', 'Tu contraseña ha sido cambiada correctamente.');
    }
}
