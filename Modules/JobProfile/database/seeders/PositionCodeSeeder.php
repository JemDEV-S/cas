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
        $positionCodes = [
            [
                'code' => 'CAP-001',
                'name' => 'Capacitador',
                'description' => 'Profesional encargado de capacitación y formación',
                'base_salary' => 3000.00,
                'essalud_percentage' => 9.0,
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'ESP-001',
                'name' => 'Especialista en Sistemas',
                'description' => 'Profesional especializado en sistemas y tecnología',
                'base_salary' => 4500.00,
                'essalud_percentage' => 9.0,
                'contract_months' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'ASI-001',
                'name' => 'Asistente Administrativo',
                'description' => 'Apoyo en labores administrativas',
                'base_salary' => 1500.00,
                'essalud_percentage' => 9.0,
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'TEC-001',
                'name' => 'Técnico de Soporte',
                'description' => 'Soporte técnico y mantenimiento',
                'base_salary' => 2000.00,
                'essalud_percentage' => 9.0,
                'contract_months' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'ABO-001',
                'name' => 'Abogado',
                'description' => 'Asesoría legal y representación',
                'base_salary' => 5000.00,
                'essalud_percentage' => 9.0,
                'contract_months' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($positionCodes as $data) {
            PositionCode::create($data);
        }

        $this->command->info('Position codes seeded successfully.');
    }
}
