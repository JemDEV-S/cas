<?php

namespace Modules\Configuration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Configuration\Entities\ConfigGroup;
use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Services\ConfigService;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    public function __construct(
        protected ConfigService $configService
    ) {}

    /**
     * Display a listing of configuration groups
     */
    public function index()
    {
        // Verificar que tenga permiso para ver configuraciones
        $this->authorize('viewAny', SystemConfig::class);

        $groups = ConfigGroup::with(['configs' => function ($query) {  // ✅ Cambiar systemConfigs a configs
            $query->editable()->ordered();
        }])
            ->active()
            ->ordered()
            ->get();

        return view('configuration::index', compact('groups'));
    }

    /**
     * Show the form for editing configurations
     */
    public function edit($groupId = null)
    {
        // Verificar que tenga permiso para ver configuraciones
        $this->authorize('viewAny', SystemConfig::class);

        $groups = ConfigGroup::with(['configs' => function ($query) {  // ✅ Cambiar systemConfigs a configs
            $query->editable()->ordered();
        }])
            ->active()
            ->ordered()
            ->get();

        $selectedGroup = $groupId
            ? $groups->firstWhere('id', $groupId)
            : $groups->first();

        return view('configuration::edit', compact('groups', 'selectedGroup'));
    }

    /**
     * Update the specified configurations
     */
    public function update(Request $request, $groupId)
    {
        // Verificar que tenga permiso para actualizar configuraciones
        $this->authorize('update', SystemConfig::class);

        try {
            DB::beginTransaction();

            $configs = $request->input('configs', []);
            $changeReason = $request->input('change_reason', 'Actualización manual');

            foreach ($configs as $configId => $value) {
                $config = SystemConfig::findOrFail($configId);

                // Verificar si es editable
                if (!$config->is_editable) {
                    continue;
                }

                // Actualizar usando el servicio
                $this->configService->set(
                    $config->key,
                    $value,
                    $changeReason,
                    auth()->id(),
                    $request->ip()
                );
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Configuraciones actualizadas correctamente');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Error al actualizar configuraciones: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reset configuration to default value
     */
    public function reset($id)
    {
        // Verificar que tenga permiso para resetear configuraciones
        $this->authorize('reset', SystemConfig::class);

        try {
            $config = SystemConfig::findOrFail($id);

            if (!$config->is_editable) {
                return redirect()
                    ->back()
                    ->with('error', 'Esta configuración no puede ser editada');
            }

            $this->configService->reset(
                $config->key,
                'Restauración a valor por defecto',
                auth()->id(),
                request()->ip()
            );

            return redirect()
                ->back()
                ->with('success', 'Configuración restaurada a valor por defecto');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al restaurar configuración: ' . $e->getMessage());
        }
    }

    /**
     * Show configuration history
     */
    public function history($id)
    {
        // Verificar que tenga permiso para ver el historial
        $this->authorize('viewHistory', SystemConfig::class);

        $config = SystemConfig::with(['history.changedBy', 'group'])  // ✅ Cambiar configGroup a group
            ->findOrFail($id);

        $history = $config->history()
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->paginate(20);

        return view('configuration::history', compact('config', 'history'));
    }

    /**
     * Show the form for creating a new resource (no usado por ahora)
     */
    public function create()
    {
        return view('configuration::create');
    }

    /**
     * Store a newly created resource in storage (no usado por ahora)
     */
    public function store(Request $request)
    {
        // No implementado - las configs se crean via seeders
    }

    /**
     * Show the specified resource (no usado por ahora)
     */
    public function show($id)
    {
        return view('configuration::show');
    }

    /**
     * Remove the specified resource from storage (no usado por ahora)
     */
    public function destroy($id)
    {
        // No implementado - las configs no se eliminan desde UI
    }
}
