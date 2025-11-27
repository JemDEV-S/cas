<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\JobProfile\Http\Requests\StorePositionCodeRequest;
use Modules\JobProfile\Http\Requests\UpdatePositionCodeRequest;
use Modules\JobProfile\Services\Contracts\PositionCodeServiceInterface;
use Modules\Core\Exceptions\BusinessRuleException;

class PositionCodeController extends Controller
{
    public function __construct(
        protected PositionCodeServiceInterface $positionCodeService
    ) {}

    /**
     * Display a listing of position codes.
     */
    public function index(Request $request): View
    {
        $positionCodes = $this->positionCodeService->getAll();

        if ($request->has('active_only') && $request->active_only) {
            $positionCodes = $this->positionCodeService->getActive();
        }

        return view('jobprofile::positions.index', compact('positionCodes'));
    }

    /**
     * Show the form for creating a new position code.
     */
    public function create(): View
    {
        return view('jobprofile::positions.create');
    }

    /**
     * Store a newly created position code.
     */
    public function store(StorePositionCodeRequest $request): RedirectResponse
    {
        try {
            $positionCode = $this->positionCodeService->create($request->validated());

            return redirect()
                ->route('jobprofile.positions.show', $positionCode->id)
                ->with('success', 'Código de posición creado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el código de posición: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified position code.
     */
    public function show(string $id): View
    {
        $positionCode = $this->positionCodeService->findById($id);

        if (!$positionCode) {
            abort(404, 'Código de posición no encontrado.');
        }

        return view('jobprofile::positions.show', compact('positionCode'));
    }

    /**
     * Show the form for editing the specified position code.
     */
    public function edit(string $id): View
    {
        $positionCode = $this->positionCodeService->findById($id);

        if (!$positionCode) {
            abort(404, 'Código de posición no encontrado.');
        }

        return view('jobprofile::positions.edit', compact('positionCode'));
    }

    /**
     * Update the specified position code.
     */
    public function update(UpdatePositionCodeRequest $request, string $id): RedirectResponse
    {
        try {
            $positionCode = $this->positionCodeService->update($id, $request->validated());

            return redirect()
                ->route('jobprofile.positions.show', $positionCode->id)
                ->with('success', 'Código de posición actualizado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el código de posición: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified position code.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->positionCodeService->delete($id);

            return redirect()
                ->route('jobprofile.positions.index')
                ->with('success', 'Código de posición eliminado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al eliminar el código de posición: ' . $e->getMessage());
        }
    }

    /**
     * Activate a position code.
     */
    public function activate(string $id): RedirectResponse
    {
        try {
            $this->positionCodeService->activate($id);

            return back()
                ->with('success', 'Código de posición activado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al activar el código de posición: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a position code.
     */
    public function deactivate(string $id): RedirectResponse
    {
        try {
            $this->positionCodeService->deactivate($id);

            return back()
                ->with('success', 'Código de posición desactivado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar el código de posición: ' . $e->getMessage());
        }
    }
}
