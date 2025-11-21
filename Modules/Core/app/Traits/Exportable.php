<?php

namespace Modules\Core\Traits;

/**
 * Exportable Trait
 *
 * Proporciona funcionalidad de exportación a diferentes formatos.
 */
trait Exportable
{
    /**
     * Exporta el modelo a un array.
     *
     * @return array
     */
    public function toExport(): array
    {
        $exportableColumns = $this->getExportableColumns();

        if (empty($exportableColumns)) {
            return $this->toArray();
        }

        return collect($this->toArray())
            ->only($exportableColumns)
            ->toArray();
    }

    /**
     * Obtiene las columnas que pueden ser exportadas.
     *
     * @return array
     */
    protected function getExportableColumns(): array
    {
        return property_exists($this, 'exportable') ? $this->exportable : [];
    }

    /**
     * Obtiene los encabezados para la exportación.
     *
     * @return array
     */
    public function getExportHeaders(): array
    {
        if (property_exists($this, 'exportHeaders')) {
            return $this->exportHeaders;
        }

        return array_map(function ($column) {
            return ucwords(str_replace('_', ' ', $column));
        }, $this->getExportableColumns());
    }

    /**
     * Scope para preparar datos para exportación.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForExport($query)
    {
        $exportableColumns = $this->getExportableColumns();

        if (!empty($exportableColumns)) {
            $query->select($exportableColumns);
        }

        return $query;
    }
}
