<?php

namespace Modules\Document\Services;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class TemplateRendererService
{
    /**
     * Renderiza un template con los datos proporcionados
     */
    public function render(string $template, array $data = []): string
    {
        try {
            // Crear un archivo temporal para el template
            $tempPath = storage_path('framework/views/temp_' . md5($template . microtime()) . '.blade.php');

            // Crear directorio si no existe
            $dir = dirname($tempPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Guardar el template temporalmente
            file_put_contents($tempPath, $template);

            // Renderizar usando View::file()
            $rendered = View::file($tempPath, $data)->render();

            // Limpiar archivo temporal
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            return $rendered;
        } catch (\Exception $e) {
            throw new \Exception('Error al renderizar template: ' . $e->getMessage());
        }
    }

    /**
     * Renderiza un template desde una vista Blade existente
     */
    public function renderView(string $viewName, array $data = []): string
    {
        return View::make($viewName, $data)->render();
    }

    /**
     * Valida que todas las variables requeridas estén presentes
     */
    public function validateData(array $requiredVariables, array $data): bool
    {
        foreach ($requiredVariables as $variable) {
            if (!isset($data[$variable])) {
                throw new \Exception("Variable requerida no encontrada: {$variable}");
            }
        }

        return true;
    }

    /**
     * Extrae variables de un template
     */
    public function extractVariables(string $template): array
    {
        preg_match_all('/\{\{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/', $template, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Reemplaza variables en un template simple (sin Blade)
     */
    public function simpleReplace(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
            $template = str_replace("{{ " . $key . " }}", $value, $template);
        }

        return $template;
    }
}
