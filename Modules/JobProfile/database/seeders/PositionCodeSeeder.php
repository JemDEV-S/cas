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
                'code' => 'PP II',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel II',
                'base_salary' => 3000.00,
                'essalud_percentage' => 8.83, // 265/3000 * 100 ≈ 8.83
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'TEC I',
                'name' => 'TECNICO ADMINISTRATIVO',
                'description' => 'Técnico administrativo nivel I',
                'base_salary' => 2500.00,
                'essalud_percentage' => 9.00, // 225/2500 * 100 = 9.00
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'TEC II',
                'name' => 'TECNICO DE SOPORTE',
                'description' => 'Técnico de soporte nivel II',
                'base_salary' => 2000.00,
                'essalud_percentage' => 9.00, // 180/2000 * 100 = 9.00
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'AA I',
                'name' => 'ASISTENTE ADMINISTRATIVO',
                'description' => 'Asistente administrativo nivel I',
                'base_salary' => 2800.00,
                'essalud_percentage' => 9.00, // 252/2800 * 100 = 9.00
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'ESP I',
                'name' => 'PROFESIONAL ESPECIALISTA',
                'description' => 'Profesional especialista nivel I',
                'base_salary' => 3800.00,
                'essalud_percentage' => 6.97, // 265/3800 * 100 ≈ 6.97
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'PA II',
                'name' => 'PROFESIONAL EXPERTO',
                'description' => 'Profesional experto nivel II',
                'base_salary' => 4500.00,
                'essalud_percentage' => 5.89, // 265/4500 * 100 ≈ 5.89
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'PP I',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel I',
                'base_salary' => 3300.00,
                'essalud_percentage' => 8.03, // 265/3300 * 100 ≈ 8.03
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'PP III',
                'name' => 'PROFESIONAL DE PLANTA',
                'description' => 'Profesional de planta nivel III',
                'base_salary' => 2900.00,
                'essalud_percentage' => 9.00, // 261/2900 * 100 = 9.00
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'AAI',
                'name' => 'AUXILIAR',
                'description' => 'Auxiliar administrativo',
                'base_salary' => 1500.00,
                'essalud_percentage' => 9.00, // 135/1500 * 100 = 9.00
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'PA I',
                'name' => 'PROFESIONAL EXPERTO',
                'description' => 'Profesional experto nivel I',
                'base_salary' => 5000.00,
                'essalud_percentage' => 5.30, // 265/5000 * 100 = 5.30
                'contract_months' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            PositionCode::create($position);
        }

        $this->command->info('Position codes seeded successfully!');
    }
}
