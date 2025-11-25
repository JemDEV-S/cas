<?php

namespace Modules\Organization\Services;

use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Repositories\OrganizationalUnitRepository;
use Illuminate\Support\Collection;

class TreeService
{
    public function __construct(
        protected OrganizationalUnitRepository $repository,
        protected HierarchyService $hierarchyService
    ) {}

    /**
     * Obtener el árbol completo como array jerárquico
     */
    public function getTree(?string $rootId = null, int $maxDepth = null): array
    {
        if ($rootId) {
            $root = $this->repository->find($rootId);
            if (!$root) {
                return [];
            }
            return $this->buildTree(collect([$root]), $maxDepth);
        }

        $roots = $this->hierarchyService->getRoots();
        return $this->buildTree($roots, $maxDepth);
    }

    /**
     * Construir árbol recursivamente
     */
    protected function buildTree(Collection $nodes, ?int $maxDepth = null, int $currentDepth = 0): array
    {
        $tree = [];

        foreach ($nodes as $node) {
            $item = $this->nodeToArray($node);

            // Si no hemos alcanzado la profundidad máxima, cargar hijos
            if ($maxDepth === null || $currentDepth < $maxDepth) {
                $children = $this->hierarchyService->getChildren($node->id);
                if ($children->isNotEmpty()) {
                    $item['children'] = $this->buildTree($children, $maxDepth, $currentDepth + 1);
                }
            }

            $tree[] = $item;
        }

        return $tree;
    }

    /**
     * Convertir nodo a array
     */
    protected function nodeToArray(OrganizationalUnit $node): array
    {
        return [
            'id' => $node->id,
            'code' => $node->code,
            'name' => $node->name,
            'description' => $node->description,
            'type' => $node->type->value,
            'type_label' => $node->type->label(),
            'level' => $node->level,
            'order' => $node->order,
            'is_active' => $node->is_active,
            'parent_id' => $node->parent_id,
            'has_children' => $this->hierarchyService->getChildren($node->id)->isNotEmpty(),
            'children_count' => $this->hierarchyService->countDescendants($node->id),
            'metadata' => $node->metadata,
        ];
    }

    /**
     * Obtener árbol aplanado (flat tree)
     */
    public function getFlatTree(?string $rootId = null): array
    {
        if ($rootId) {
            $descendants = $this->hierarchyService->getDescendants($rootId, true);
        } else {
            $descendants = OrganizationalUnit::orderBy('path')->get();
        }

        return $descendants->map(function ($node) {
            return $this->nodeToArray($node);
        })->toArray();
    }

    /**
     * Obtener breadcrumb para una unidad
     */
    public function getBreadcrumb(string $unitId): array
    {
        $path = $this->hierarchyService->getPath($unitId);

        return $path->map(function ($node) {
            return [
                'id' => $node->id,
                'name' => $node->name,
                'code' => $node->code,
            ];
        })->toArray();
    }

    /**
     * Generar path string para una unidad
     */
    public function generatePathString(string $unitId): string
    {
        $ancestors = $this->hierarchyService->getAncestors($unitId, true);

        return '/' . $ancestors->pluck('id')->implode('/');
    }

    /**
     * Obtener opciones para select/dropdown
     */
    public function getSelectOptions(?string $excludeId = null, bool $onlyActive = true): array
    {
        $query = OrganizationalUnit::query();

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        if ($excludeId) {
            // Excluir la unidad y todos sus descendientes
            $descendants = $this->hierarchyService->getDescendants($excludeId, true);
            $excludeIds = $descendants->pluck('id')->toArray();
            $query->whereNotIn('id', $excludeIds);
        }

        $units = $query->orderBy('path')->get();

        return $units->map(function ($unit) {
            return [
                'value' => $unit->id,
                'label' => str_repeat('— ', $unit->level) . $unit->name,
                'code' => $unit->code,
                'level' => $unit->level,
            ];
        })->toArray();
    }

    /**
     * Validar estructura del árbol
     */
    public function validateTree(): array
    {
        $errors = [];

        // Verificar unidades sin padre que no sean raíz
        $orphans = OrganizationalUnit::whereNotNull('parent_id')
            ->whereDoesntHave('parent')
            ->get();

        if ($orphans->isNotEmpty()) {
            $errors[] = [
                'type' => 'orphan_units',
                'message' => 'Existen unidades con parent_id inválido',
                'units' => $orphans->pluck('id')->toArray(),
            ];
        }

        // Verificar ciclos (una unidad que sea su propio ancestro)
        $units = OrganizationalUnit::all();
        foreach ($units as $unit) {
            if ($this->hierarchyService->isDescendant($unit->id, $unit->id)) {
                $errors[] = [
                    'type' => 'circular_reference',
                    'message' => "Referencia circular detectada en unidad {$unit->code}",
                    'unit_id' => $unit->id,
                ];
            }
        }

        // Verificar niveles incorrectos
        foreach ($units as $unit) {
            $expectedLevel = $this->hierarchyService->getLevel($unit->id);
            if ($unit->level !== $expectedLevel) {
                $errors[] = [
                    'type' => 'incorrect_level',
                    'message' => "Nivel incorrecto en unidad {$unit->code}",
                    'unit_id' => $unit->id,
                    'current_level' => $unit->level,
                    'expected_level' => $expectedLevel,
                ];
            }
        }

        return $errors;
    }

    /**
     * Exportar árbol a diferentes formatos
     */
    public function export(string $format = 'json', ?string $rootId = null): string
    {
        $tree = $this->getTree($rootId);

        return match ($format) {
            'json' => json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'xml' => $this->toXml($tree),
            'yaml' => $this->toYaml($tree),
            default => json_encode($tree),
        };
    }

    /**
     * Convertir a XML
     */
    protected function toXml(array $tree, int $level = 0): string
    {
        $xml = '';
        $indent = str_repeat('  ', $level);

        foreach ($tree as $node) {
            $xml .= "{$indent}<unit>\n";
            $xml .= "{$indent}  <id>{$node['id']}</id>\n";
            $xml .= "{$indent}  <code>{$node['code']}</code>\n";
            $xml .= "{$indent}  <name><![CDATA[{$node['name']}]]></name>\n";

            if (!empty($node['children'])) {
                $xml .= "{$indent}  <children>\n";
                $xml .= $this->toXml($node['children'], $level + 2);
                $xml .= "{$indent}  </children>\n";
            }

            $xml .= "{$indent}</unit>\n";
        }

        return $xml;
    }

    /**
     * Convertir a YAML
     */
    protected function toYaml(array $tree, int $level = 0): string
    {
        $yaml = '';
        $indent = str_repeat('  ', $level);

        foreach ($tree as $i => $node) {
            $yaml .= "{$indent}- id: {$node['id']}\n";
            $yaml .= "{$indent}  code: {$node['code']}\n";
            $yaml .= "{$indent}  name: \"{$node['name']}\"\n";

            if (!empty($node['children'])) {
                $yaml .= "{$indent}  children:\n";
                $yaml .= $this->toYaml($node['children'], $level + 2);
            }
        }

        return $yaml;
    }

    /**
     * Buscar en el árbol
     */
    public function search(string $query, array $fields = ['name', 'code', 'description']): array
    {
        $units = OrganizationalUnit::where(function ($q) use ($query, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'LIKE', "%{$query}%");
            }
        })->get();

        return $units->map(function ($unit) {
            $breadcrumb = $this->getBreadcrumb($unit->id);

            return array_merge($this->nodeToArray($unit), [
                'breadcrumb' => $breadcrumb,
                'path_string' => implode(' > ', array_column($breadcrumb, 'name')),
            ]);
        })->toArray();
    }
}
