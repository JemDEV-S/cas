<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\JobProfile\Services\JobProfileService;
use Modules\Core\Exceptions\BusinessRuleException;

class JobProfileController extends Controller
{
    public function __construct(
        protected JobProfileService $jobProfileService
    ) {}

    /**
     * Display a listing of job profiles.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $jobProfiles = $status
            ? $this->jobProfileService->getByStatus($status)
            : $this->jobProfileService->getAll();

        return view('jobprofile::index', compact('jobProfiles', 'status'));
    }

    /**
     * Show the form for creating a new job profile.
     */
    public function create(): View
    {
        return view('jobprofile::create');
    }

    /**
     * Store a newly created job profile.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $jobProfile = $this->jobProfileService->create(
                $request->except(['requirements', 'responsibilities']),
                $request->get('requirements', []),
                $request->get('responsibilities', [])
            );

            return redirect()
                ->route('jobprofile.show', $jobProfile->id)
                ->with('success', 'Perfil de puesto creado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified job profile.
     */
    public function show(string $id): View
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        return view('jobprofile::show', compact('jobProfile'));
    }

    /**
     * Show the form for editing the specified job profile.
     */
    public function edit(string $id): View
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        if (!$jobProfile->canEdit()) {
            abort(403, 'No se puede editar este perfil en su estado actual.');
        }

        return view('jobprofile::edit', compact('jobProfile'));
    }

    /**
     * Update the specified job profile.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $jobProfile = $this->jobProfileService->update(
                $id,
                $request->except(['requirements', 'responsibilities'])
            );

            if ($request->has('requirements')) {
                $this->jobProfileService->updateRequirements($id, $request->get('requirements'));
            }

            if ($request->has('responsibilities')) {
                $this->jobProfileService->updateResponsibilities($id, $request->get('responsibilities'));
            }

            return redirect()
                ->route('jobprofile.show', $jobProfile->id)
                ->with('success', 'Perfil de puesto actualizado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified job profile.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->jobProfileService->delete($id);

            return redirect()
                ->route('jobprofile.index')
                ->with('success', 'Perfil de puesto eliminado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el perfil: ' . $e->getMessage());
        }
    }
}
