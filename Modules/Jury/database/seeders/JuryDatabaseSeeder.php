<?php

namespace Modules\Jury\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Jury\Entities\{JuryMember, JuryAssignment, JuryConflict};
use Modules\Jury\Enums\{MemberType, JuryRole, ConflictType, ConflictSeverity};
use App\Models\User;
use Illuminate\Support\Str;

class JuryDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Crear usuarios de prueba si no existen
        $users = $this->createTestUsers();

        // Crear jurados
        $juryMembers = $this->createJuryMembers($users);

        // Obtener job_postings para asignaciones
        $jobPostings = \Modules\JobPosting\Entities\JobPosting::limit(3)->get();

        if ($jobPostings->count() > 0) {
            // Crear asignaciones
            $this->createAssignments($juryMembers, $jobPostings);

            // Crear algunos conflictos de prueba
            $applications = \Modules\Application\Entities\Application::limit(5)->get();
            if ($applications->count() > 0) {
                $this->createConflicts($juryMembers, $applications);
            }
        }

        $this->command->info('✅ Seeder de Jury completado!');
    }

    protected function createTestUsers(): array
    {
        $users = [];

        $testUsers = [
            ['name' => 'Dr. Juan Pérez Rodríguez', 'email' => 'juan.perez@jury.test'],
            ['name' => 'Dra. María García López', 'email' => 'maria.garcia@jury.test'],
            ['name' => 'Ing. Carlos Mendoza Silva', 'email' => 'carlos.mendoza@jury.test'],
            ['name' => 'Lic. Ana Torres Vargas', 'email' => 'ana.torres@jury.test'],
            ['name' => 'Dr. Roberto Sánchez Cruz', 'email' => 'roberto.sanchez@jury.test'],
            ['name' => 'Dra. Patricia Ramírez Flores', 'email' => 'patricia.ramirez@jury.test'],
            ['name' => 'Mg. Luis Fernández Díaz', 'email' => 'luis.fernandez@jury.test'],
            ['name' => 'Lic. Carmen Morales Ruiz', 'email' => 'carmen.morales@jury.test'],
        ];

        foreach ($testUsers as $userData) {
            $users[] = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    protected function createJuryMembers(array $users): array
    {
        $specialties = [
            'Derecho Administrativo',
            'Gestión Pública',
            'Recursos Humanos',
            'Administración',
            'Contabilidad',
            'Economía',
            'Ingeniería Civil',
            'Sistemas de Información',
        ];

        $titles = [
            'Abogado',
            'Contador Público',
            'Administrador',
            'Ingeniero',
            'Economista',
            'Licenciado en Gestión',
        ];

        $juryMembers = [];

        foreach ($users as $index => $user) {
            // Verificar que no exista ya un jury member para este usuario
            $existing = JuryMember::where('user_id', $user->id)->first();
            if ($existing) {
                $juryMembers[] = $existing;
                continue;
            }

            $member = JuryMember::create([
                'user_id' => $user->id,
                'specialty' => $specialties[$index % count($specialties)],
                'years_of_experience' => rand(5, 25),
                'professional_title' => $titles[$index % count($titles)],
                'bio' => 'Profesional con amplia experiencia en evaluación de procesos de selección y gestión pública.',
                'is_active' => $index < 6, // 6 activos, 2 inactivos
                'is_available' => $index < 5, // 5 disponibles
                'unavailability_reason' => $index >= 5 ? 'Licencia por motivos personales' : null,
                'training_completed' => $index < 6, // 6 capacitados
                'training_completed_at' => $index < 6 ? now()->subDays(rand(30, 180)) : null,
                'total_evaluations' => rand(5, 50),
                'total_assignments' => rand(2, 15),
                'average_evaluation_time' => rand(30, 120),
                'consistency_score' => rand(70, 98) + (rand(0, 99) / 100),
                'average_rating' => rand(35, 50) / 10,
                'max_concurrent_assignments' => rand(15, 25),
                'preferred_areas' => ['administrativa', 'técnica'],
            ]);

            $juryMembers[] = $member;
        }

        $this->command->info("✅ Creados {count($juryMembers)} jurados");

        return $juryMembers;
    }

    protected function createAssignments(array $juryMembers, $jobPostings): void
    {
        $assignmentCount = 0;

        foreach ($jobPostings as $jobPosting) {
            // Asignar 3 titulares
            $titulares = array_slice($juryMembers, 0, 3);
            $roles = [JuryRole::PRESIDENTE, JuryRole::SECRETARIO, JuryRole::VOCAL];

            foreach ($titulares as $index => $member) {
                // Verificar que no exista ya
                $existing = JuryAssignment::where('jury_member_id', $member->id)
                    ->where('job_posting_id', $jobPosting->id)
                    ->where('member_type', MemberType::TITULAR)
                    ->first();

                if (!$existing) {
                    JuryAssignment::create([
                        'jury_member_id' => $member->id,
                        'job_posting_id' => $jobPosting->id,
                        'member_type' => MemberType::TITULAR,
                        'role_in_jury' => $roles[$index],
                        'order' => $index + 1,
                        'assigned_by' => User::first()->id,
                        'assigned_at' => now()->subDays(rand(10, 60)),
                        'assignment_resolution' => 'RES-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . '-2024',
                        'resolution_date' => now()->subDays(rand(15, 70)),
                        'status' => 'ACTIVE',
                        'is_active' => true,
                        'max_evaluations' => rand(10, 20),
                        'current_evaluations' => rand(0, 10),
                        'completed_evaluations' => rand(0, 5),
                    ]);

                    $assignmentCount++;
                }
            }

            // Asignar 2 suplentes
            $suplentes = array_slice($juryMembers, 3, 2);
            foreach ($suplentes as $index => $member) {
                $existing = JuryAssignment::where('jury_member_id', $member->id)
                    ->where('job_posting_id', $jobPosting->id)
                    ->where('member_type', MemberType::SUPLENTE)
                    ->first();

                if (!$existing) {
                    JuryAssignment::create([
                        'jury_member_id' => $member->id,
                        'job_posting_id' => $jobPosting->id,
                        'member_type' => MemberType::SUPLENTE,
                        'role_in_jury' => JuryRole::MIEMBRO,
                        'order' => 4 + $index,
                        'assigned_by' => User::first()->id,
                        'assigned_at' => now()->subDays(rand(10, 60)),
                        'status' => 'ACTIVE',
                        'is_active' => true,
                        'max_evaluations' => rand(5, 15),
                        'current_evaluations' => rand(0, 5),
                    ]);

                    $assignmentCount++;
                }
            }
        }

        $this->command->info("✅ Creadas {$assignmentCount} asignaciones");
    }

    protected function createConflicts(array $juryMembers, $applications): void
    {
        $conflictTypes = [
            ConflictType::FAMILY,
            ConflictType::LABOR,
            ConflictType::PROFESSIONAL,
            ConflictType::PERSONAL,
        ];

        $conflictCount = 0;

        // Crear 3-5 conflictos de prueba
        for ($i = 0; $i < rand(3, 5); $i++) {
            $member = $juryMembers[array_rand($juryMembers)];
            $application = $applications->random();
            $conflictType = $conflictTypes[array_rand($conflictTypes)];

            // Verificar que no exista ya
            $existing = JuryConflict::where('jury_member_id', $member->id)
                ->where('application_id', $application->id)
                ->first();

            if (!$existing) {
                JuryConflict::create([
                    'jury_member_id' => $member->id,
                    'application_id' => $application->id,
                    'job_posting_id' => $application->job_profile_vacancy_id,
                    'applicant_id' => $application->applicant_id,
                    'conflict_type' => $conflictType,
                    'severity' => $conflictType->recommendedSeverity(),
                    'description' => $this->getConflictDescription($conflictType),
                    'status' => ['REPORTED', 'UNDER_REVIEW', 'CONFIRMED'][rand(0, 2)],
                    'reported_by' => User::first()->id,
                    'reported_at' => now()->subDays(rand(1, 30)),
                    'is_self_reported' => (bool) rand(0, 1),
                ]);

                $conflictCount++;
            }
        }

        $this->command->info("✅ Creados {$conflictCount} conflictos de prueba");
    }

    protected function getConflictDescription(ConflictType $type): string
    {
        return match($type) {
            ConflictType::FAMILY => 'El postulante es familiar de segundo grado del jurado',
            ConflictType::LABOR => 'El jurado y el postulante trabajaron juntos en la misma institución',
            ConflictType::PROFESSIONAL => 'Existe relación profesional de consultoría entre ambas partes',
            ConflictType::PERSONAL => 'El jurado y el postulante tienen amistad cercana',
            default => 'Se ha identificado un posible conflicto de interés',
        };
    }
}
