<?php

namespace Modules\JobProfile\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\JobProfile\Entities\PositionCode;

class PositionCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'code' => 'PA I',
                'name' => 'PROFESIONAL EXPERTO',
                'description' => 'Profesional experto nivel I - Título profesional afín al cargo con habilitación profesional vigente',
                'base_salary' => 5000.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 7.0,
                'min_specific_experience' => 3.0,
                'requires_professional_title' => true,
                'requires_professional_license' => true,
                'education_level_required' => 'titulo_profesional',
                'education_levels_accepted' => ['titulo_profesional'],
            ],
            [
                'code' => 'PA II',
                'name' => 'PROFESIONAL EXPERTO',
                'description' => 'Profesional experto nivel II - Título profesional afín al cargo con habilitación profesional vigente',
                'base_salary' => 4500.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 6.0,
                'min_specific_experience' => 2.0,
                'requires_professional_title' => true,
                'requires_professional_license' => true,
                'education_level_required' => 'titulo_profesional',
                'education_levels_accepted' => ['titulo_profesional'],
            ],
            [
                'code' => 'ESP I',
                'name' => 'PROFESIONAL ESPECIALISTA',
                'description' => 'Profesional especialista nivel I - Título profesional afín al cargo con habilitación profesional vigente',
                'base_salary' => 3800.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 5.0,
                'min_specific_experience' => 1.0,
                'requires_professional_title' => true,
                'requires_professional_license' => true,
                'education_level_required' => 'titulo_profesional',
                'education_levels_accepted' => ['titulo_profesional'],
            ],
            [
                'code' => 'PP I',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel I - Título profesional afín al cargo con habilitación profesional vigente',
                'base_salary' => 3300.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 4.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => true,
                'requires_professional_license' => true,
                'education_level_required' => 'titulo_profesional',
                'education_levels_accepted' => ['titulo_profesional'],
            ],
            [
                'code' => 'PP II',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel II - Grado de Bachiller o Título profesional afín al cargo',
                'base_salary' => 3000.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 3.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false, // Puede ser bachiller
                'requires_professional_license' => false,
                'education_level_required' => 'bachiller',
                'education_levels_accepted' => ['bachiller', 'titulo_profesional'],
            ],
            [
                'code' => 'PP III',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel III - Grado de Bachiller o Título profesional afín al cargo',
                'base_salary' => 2900.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 2.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false, // Puede ser bachiller
                'requires_professional_license' => false,
                'education_level_required' => 'bachiller',
                'education_levels_accepted' => ['bachiller', 'titulo_profesional'],
            ],
            [
                'code' => 'AA I',
                'name' => 'ASISTENTE ADMINISTRATIVO',
                'description' => 'Asistente administrativo nivel I - Título Técnico afín al cargo y/o grado de bachiller',
                'base_salary' => 2800.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 1.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'titulo_tecnico',
                'education_levels_accepted' => ['titulo_tecnico', 'bachiller'],
            ],
            [
                'code' => 'TEC I',
                'name' => 'TECNICO ADMINISTRATIVO',
                'description' => 'Técnico administrativo nivel I - Egresado universitario y/o egresado de instituto superior',
                'base_salary' => 2500.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 1.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'egresado_tecnico',
                'education_levels_accepted' => ['egresado_tecnico', 'egresado_universitario'],
            ],
            [
                'code' => 'TEC II',
                'name' => 'TECNICO DE SOPORTE',
                'description' => 'Técnico de soporte nivel II - Egresado universitario y/o egresado de instituto superior',
                'base_salary' => 2000.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 0.5,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'egresado_tecnico',
                'education_levels_accepted' => ['egresado_tecnico', 'egresado_universitario'],
            ],
            [
                'code' => 'AUXI',
                'name' => 'AUXILIAR',
                'description' => 'Auxiliar nivel I - Estudios universitarios y/o estudios técnicos',
                'base_salary' => 1500.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 0.5,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'estudios_tecnicos',
                'education_levels_accepted' => ['estudios_tecnicos', 'estudios_universitarios'],
            ],
            [
                'code' => 'AUXII',
                'name' => 'AUXILIAR',
                'description' => 'Auxiliar nivel II - Estudios universitarios y/o estudios técnicos',
                'base_salary' => 1400.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 0.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'estudios_tecnicos',
                'education_levels_accepted' => ['estudios_tecnicos', 'estudios_universitarios'],
            ],
            [
                'code' => 'AUXIII',
                'name' => 'AUXILIAR',
                'description' => 'Auxiliar nivel III - Secundaria completa',
                'base_salary' => 1200.00,
                'essalud_percentage' => 9.00,
                'contract_months' => 3,
                'is_active' => true,
                // Nuevos campos
                'min_professional_experience' => 0.0,
                'min_specific_experience' => 0.0,
                'requires_professional_title' => false,
                'requires_professional_license' => false,
                'education_level_required' => 'secundaria',
                'education_levels_accepted' => ['secundaria'],
            ],
        ];

        foreach ($positions as $position) {
            PositionCode::updateOrCreate(
                ['code' => $position['code']],
                $position
            );
        }

        $this->command->info('Position codes seeded successfully with complete data!');
    }
}