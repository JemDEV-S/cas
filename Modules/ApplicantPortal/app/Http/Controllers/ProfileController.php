<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\Services\UserService;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        // Load relationships
        $user->load([
            'educations',
            'workExperiences',
            'courses',
            'languages',
            'documents'
        ]);

        return view('applicantportal::profile.show', compact('user'));
    }

    /**
     * Show the form for editing personal information.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('applicantportal::profile.edit', compact('user'));
    }

    /**
     * Update the user's personal information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $user = Auth::user();
            $this->userService->updateProfile($user->id, $validated);

            return redirect()
                ->route('applicant.profile.show')
                ->with('success', 'Tu perfil ha sido actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for changing password.
     */
    public function editPassword()
    {
        return view('applicantportal::profile.edit-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->back()
                ->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        try {
            $this->userService->updatePassword($user->id, $validated['password']);

            return redirect()
                ->route('applicant.profile.show')
                ->with('success', 'Tu contraseña ha sido cambiada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al cambiar la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Manage user's education.
     */
    public function education()
    {
        $user = Auth::user();
        $educations = $user->educations;

        return view('applicantportal::profile.education', compact('educations'));
    }

    /**
     * Manage user's work experience.
     */
    public function workExperience()
    {
        $user = Auth::user();
        $workExperiences = $user->workExperiences;

        return view('applicantportal::profile.work-experience', compact('workExperiences'));
    }

    /**
     * Manage user's courses and certifications.
     */
    public function courses()
    {
        $user = Auth::user();
        $courses = $user->courses;

        return view('applicantportal::profile.courses', compact('courses'));
    }

    /**
     * Manage user's documents.
     */
    public function documents()
    {
        $user = Auth::user();
        $documents = $user->documents;

        return view('applicantportal::profile.documents', compact('documents'));
    }

    /**
     * Upload a new document.
     */
    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        try {
            $user = Auth::user();
            $this->userService->uploadDocument($user->id, $validated);

            return redirect()
                ->route('applicant.profile.documents')
                ->with('success', 'Documento subido exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al subir el documento: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document.
     */
    public function deleteDocument(string $documentId)
    {
        try {
            $user = Auth::user();
            $this->userService->deleteDocument($user->id, $documentId);

            return redirect()
                ->route('applicant.profile.documents')
                ->with('success', 'Documento eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }
}
