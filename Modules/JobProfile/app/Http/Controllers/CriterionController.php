<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\JobProfile\Services\CriterionService;
use Modules\Core\Exceptions\BusinessRuleException;

class CriterionController extends Controller
{
    public function __construct(
        protected CriterionService $criterionService
    ) {}

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->criterionService->create($request->all());
            return back()->with('success', 'Criterio creado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->criterionService->update($id, $request->all());
            return back()->with('success', 'Criterio actualizado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->criterionService->delete($id);
            return back()->with('success', 'Criterio eliminado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
