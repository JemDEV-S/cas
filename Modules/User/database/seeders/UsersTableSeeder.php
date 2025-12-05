<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existe el rol area-user
        $role = Role::where('slug', 'area-user')->first();
        if (!$role) {
            $this->command->warn('❌ Rol area-user no encontrado');
            return;
        }

        $users = [
            [
                'dni' => '24389598',
                'first_name' => 'Bernardino',
                'last_name' => 'Marin Castillo',
                'email' => 'b.marin@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '70577292',
                'first_name' => 'Jose J.A.',
                'last_name' => 'Palomino Pacaya',
                'email' => 'j.palomino@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '42553809',
                'first_name' => 'Gilmar',
                'last_name' => 'Hermosa Luna',
                'email' => 'gilmarhermozal@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23864357',
                'first_name' => 'Herbert',
                'last_name' => 'Nina Arcaya',
                'email' => 'h.nina@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23891075',
                'first_name' => 'Maruja',
                'last_name' => 'Ccohuanqui Auccatinco',
                'email' => 'm.ccohuanqui@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23916638',
                'first_name' => 'Guillermo',
                'last_name' => 'Carrillo Carrillo',
                'email' => 'g.carrillo@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '43834441',
                'first_name' => 'Fredrick Danilo',
                'last_name' => 'Espinoza Paz',
                'email' => 'f.espinoza@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '44840017',
                'first_name' => 'Enrique Melquiades',
                'last_name' => 'Molina Roca',
                'email' => 'e.molina@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23926192',
                'first_name' => 'Dileam Amir',
                'last_name' => 'Uscamayta Loayza',
                'email' => 'd.uscamayta@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '40852864',
                'first_name' => 'Hipolito',
                'last_name' => 'Suicco Canchi',
                'email' => 'h.suicco@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23822140',
                'first_name' => 'Maria Violeta',
                'last_name' => 'Casapino Mujica',
                'email' => 'v.casapino@munisanjeronimocusco.gob.pe',
            ],
            [
                'dni' => '23892657',
                'first_name' => 'Augusto',
                'last_name' => 'Auccapure Vallenas',
                'email' => 'a.auccapure@munisanjeronimocusco.gob.pe',
            ],
        ];

        $this->command->info('🔄 Creando usuarios del área...');
        $this->command->newLine();

        foreach ($users as $userData) {
            // Generar contraseña: DNI + 3 letras aleatorias del nombre
            $password = $this->generatePassword($userData['dni'], $userData['first_name']);

            $user = User::updateOrCreate(
                ['dni' => $userData['dni']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Asignar rol area-user
            $user->syncRoles([$role->id]);

            $this->command->info("✅ Usuario creado: {$userData['dni']} / {$password} - {$userData['first_name']} {$userData['last_name']}");
        }

        $this->command->newLine();
        $this->command->info('✅ Todos los usuarios han sido creados exitosamente!');
    }

    /**
     * Generar contraseña: DNI + 3 letras aleatorias del nombre
     */
    private function generatePassword(string $dni, string $firstName): string
    {
        // Remover espacios y caracteres especiales del nombre
        $cleanName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        
        // Obtener todas las letras del nombre
        $letters = str_split(strtolower($cleanName));
        
        // Si el nombre tiene menos de 3 letras, usar las que tenga
        if (count($letters) < 3) {
            $randomLetters = implode('', $letters);
        } else {
            // Mezclar y tomar 3 letras aleatorias
            shuffle($letters);
            $randomLetters = implode('', array_slice($letters, 0, 3));
        }
        
        return $dni . $randomLetters;
    }
}