<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentTemplate;

class ConvocatoriaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_CONVOCATORIA_COMPLETA'],
            [
                'name' => 'Convocatoria Completa - Bases Integradas',
                'category' => 'convocatoria_completa',
                'status' => 'active',
                'content' => '', // Se carga directamente la vista por convención
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'paper_size' => 'A4',
                'orientation' => 'portrait', // Vertical
                'margins' => json_encode([
                    'top' => 20,
                    'right' => 15,
                    'bottom' => 20,
                    'left' => 15,
                ]),
            ]
        );
    }
}
