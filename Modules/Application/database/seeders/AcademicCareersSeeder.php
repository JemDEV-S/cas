<?php

namespace Modules\Application\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Application\Entities\AcademicCareer;
use Illuminate\Support\Str;

class AcademicCareersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea las 45 carreras base según análisis de uso real del sistema CAS.
     */
    public function run(): void
    {
        $careers = $this->getCareers();

        foreach ($careers as $career) {
            AcademicCareer::create(array_merge($career, [
                'id' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✓ ' . count($careers) . ' carreras académicas creadas exitosamente');
    }

    /**
     * Obtener el array de 45 carreras curadas
     */
    private function getCareers(): array
    {
        return [
            // ========== Ciencias Empresariales y Económicas ==========
            [
                'code' => 'CAR_ADMINISTRACION',
                'name' => 'Administración',
                'short_name' => 'Administración',
                'sunedu_category' => 'ADMINISTRACIÓN Y COMERCIO',
                'category_group' => 'Ciencias Empresariales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en administración de empresas y gestión organizacional',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_CONTABILIDAD',
                'name' => 'Contabilidad',
                'short_name' => 'Contabilidad',
                'sunedu_category' => 'ADMINISTRACIÓN Y COMERCIO',
                'category_group' => 'Ciencias Empresariales',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en contabilidad y finanzas',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ECONOMIA',
                'name' => 'Economía',
                'short_name' => 'Economía',
                'sunedu_category' => 'CIENCIAS SOCIALES Y DEL COMPORTAMIENTO',
                'category_group' => 'Ciencias Empresariales',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en economía y análisis económico',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_MARKETING',
                'name' => 'Marketing',
                'short_name' => 'Marketing',
                'sunedu_category' => 'ADMINISTRACIÓN Y COMERCIO',
                'category_group' => 'Ciencias Empresariales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en marketing y gestión comercial',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_NEGOCIOS_INTERNACIONALES',
                'name' => 'Negocios Internacionales',
                'short_name' => 'Negocios Internacionales',
                'sunedu_category' => 'ADMINISTRACIÓN Y COMERCIO',
                'category_group' => 'Ciencias Empresariales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en comercio internacional y negocios globales',
                'display_order' => 5,
                'is_active' => true,
            ],

            // ========== Ciencias Jurídicas ==========
            [
                'code' => 'CAR_DERECHO',
                'name' => 'Derecho',
                'short_name' => 'Derecho',
                'sunedu_category' => 'DERECHO',
                'category_group' => 'Ciencias Jurídicas',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ciencias jurídicas',
                'display_order' => 10,
                'is_active' => true,
            ],

            // ========== Ingeniería - Sistemas e Informática ==========
            [
                'code' => 'CAR_ING_SISTEMAS',
                'name' => 'Ingeniería de Sistemas',
                'short_name' => 'Ing. Sistemas',
                'sunedu_category' => 'INFORMÁTICA',
                'category_group' => 'Ingeniería de Sistemas',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería de sistemas de información',
                'display_order' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_INFORMATICA',
                'name' => 'Ingeniería Informática',
                'short_name' => 'Ing. Informática',
                'sunedu_category' => 'INFORMÁTICA',
                'category_group' => 'Ingeniería de Sistemas',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería informática',
                'display_order' => 21,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_SOFTWARE',
                'name' => 'Ingeniería de Software',
                'short_name' => 'Ing. Software',
                'sunedu_category' => 'INFORMÁTICA',
                'category_group' => 'Ingeniería de Sistemas',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería de software',
                'display_order' => 22,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_COMPUTACION_INFORMATICA',
                'name' => 'Computación e Informática',
                'short_name' => 'Computación',
                'sunedu_category' => 'INFORMÁTICA',
                'category_group' => 'Ingeniería de Sistemas',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en computación e informática',
                'display_order' => 23,
                'is_active' => true,
            ],

            // ========== Ingeniería - Civil y Construcción ==========
            [
                'code' => 'CAR_ING_CIVIL',
                'name' => 'Ingeniería Civil',
                'short_name' => 'Ing. Civil',
                'sunedu_category' => 'ARQUITECTURA Y CONSTRUCCIÓN',
                'category_group' => 'Ingeniería Civil',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería civil',
                'display_order' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ARQUITECTURA',
                'name' => 'Arquitectura',
                'short_name' => 'Arquitectura',
                'sunedu_category' => 'ARQUITECTURA Y CONSTRUCCIÓN',
                'category_group' => 'Arquitectura y Urbanismo',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en arquitectura',
                'display_order' => 31,
                'is_active' => true,
            ],

            // ========== Ingeniería - Industrial y Producción ==========
            [
                'code' => 'CAR_ING_INDUSTRIAL',
                'name' => 'Ingeniería Industrial',
                'short_name' => 'Ing. Industrial',
                'sunedu_category' => 'INDUSTRIA Y PRODUCCIÓN',
                'category_group' => 'Ingeniería Industrial',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería industrial',
                'display_order' => 40,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_MECANICA',
                'name' => 'Ingeniería Mecánica',
                'short_name' => 'Ing. Mecánica',
                'sunedu_category' => 'INDUSTRIA Y PRODUCCIÓN',
                'category_group' => 'Ingeniería Mecánica',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería mecánica',
                'display_order' => 41,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_MECATRONICA',
                'name' => 'Ingeniería Mecatrónica',
                'short_name' => 'Ing. Mecatrónica',
                'sunedu_category' => 'INDUSTRIA Y PRODUCCIÓN',
                'category_group' => 'Ingeniería Mecánica',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería mecatrónica',
                'display_order' => 42,
                'is_active' => true,
            ],

            // ========== Ingeniería - Ambiental ==========
            [
                'code' => 'CAR_ING_AMBIENTAL',
                'name' => 'Ingeniería Ambiental',
                'short_name' => 'Ing. Ambiental',
                'sunedu_category' => 'MEDIO AMBIENTE',
                'category_group' => 'Ingeniería Ambiental',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería ambiental',
                'display_order' => 50,
                'is_active' => true,
            ],

            // ========== Ingeniería - Minas y Geología ==========
            [
                'code' => 'CAR_ING_MINAS',
                'name' => 'Ingeniería de Minas',
                'short_name' => 'Ing. Minas',
                'sunedu_category' => 'INGENIERÍA Y PROFESIONES AFINES',
                'category_group' => 'Ingeniería de Minas',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería de minas',
                'display_order' => 60,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_GEOLOGICA',
                'name' => 'Ingeniería Geológica',
                'short_name' => 'Ing. Geológica',
                'sunedu_category' => 'INGENIERÍA Y PROFESIONES AFINES',
                'category_group' => 'Geología',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería geológica',
                'display_order' => 61,
                'is_active' => true,
            ],

            // ========== Ciencias de la Salud ==========
            [
                'code' => 'CAR_MEDICINA',
                'name' => 'Medicina Humana',
                'short_name' => 'Medicina',
                'sunedu_category' => 'SALUD',
                'category_group' => 'Ciencias de la Salud',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en medicina humana',
                'display_order' => 70,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ENFERMERIA',
                'name' => 'Enfermería',
                'short_name' => 'Enfermería',
                'sunedu_category' => 'SALUD',
                'category_group' => 'Ciencias de la Salud',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en enfermería',
                'display_order' => 71,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_OBSTETRICIA',
                'name' => 'Obstetricia',
                'short_name' => 'Obstetricia',
                'sunedu_category' => 'SALUD',
                'category_group' => 'Ciencias de la Salud',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en obstetricia',
                'display_order' => 72,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_NUTRICION',
                'name' => 'Nutrición y Dietética',
                'short_name' => 'Nutrición',
                'sunedu_category' => 'SALUD',
                'category_group' => 'Ciencias de la Salud',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en nutrición y dietética',
                'display_order' => 73,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ODONTOLOGIA',
                'name' => 'Odontología',
                'short_name' => 'Odontología',
                'sunedu_category' => 'SALUD',
                'category_group' => 'Ciencias de la Salud',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en odontología',
                'display_order' => 74,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_PSICOLOGIA',
                'name' => 'Psicología',
                'short_name' => 'Psicología',
                'sunedu_category' => 'CIENCIAS SOCIALES Y DEL COMPORTAMIENTO',
                'category_group' => 'Ciencias Sociales',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en psicología',
                'display_order' => 75,
                'is_active' => true,
            ],

            // ========== Ciencias Veterinarias ==========
            [
                'code' => 'CAR_MEDICINA_VETERINARIA',
                'name' => 'Medicina Veterinaria',
                'short_name' => 'Veterinaria',
                'sunedu_category' => 'VETERINARIA',
                'category_group' => 'Veterinaria',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en medicina veterinaria',
                'display_order' => 80,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ZOOTECNIA',
                'name' => 'Zootecnia',
                'short_name' => 'Zootecnia',
                'sunedu_category' => 'AGRICULTURA',
                'category_group' => 'Ciencias Agrarias',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en zootecnia',
                'display_order' => 81,
                'is_active' => true,
            ],

            // ========== Educación ==========
            [
                'code' => 'CAR_EDUCACION',
                'name' => 'Educación',
                'short_name' => 'Educación',
                'sunedu_category' => 'OTROS PROGRAMAS EN EDUCACIÓN',
                'category_group' => 'Educación',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en educación',
                'display_order' => 90,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_EDUCACION_INICIAL',
                'name' => 'Educación Inicial',
                'short_name' => 'Educ. Inicial',
                'sunedu_category' => 'EDUCACIÓN INICIAL Y PRIMARIA',
                'category_group' => 'Educación',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en educación inicial',
                'display_order' => 91,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_EDUCACION_PRIMARIA',
                'name' => 'Educación Primaria',
                'short_name' => 'Educ. Primaria',
                'sunedu_category' => 'EDUCACIÓN INICIAL Y PRIMARIA',
                'category_group' => 'Educación',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en educación primaria',
                'display_order' => 92,
                'is_active' => true,
            ],

            // ========== Ciencias Sociales ==========
            [
                'code' => 'CAR_TRABAJO_SOCIAL',
                'name' => 'Trabajo Social',
                'short_name' => 'Trabajo Social',
                'sunedu_category' => 'CIENCIAS SOCIALES Y DEL COMPORTAMIENTO',
                'category_group' => 'Ciencias Sociales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en trabajo social',
                'display_order' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_SOCIOLOGIA',
                'name' => 'Sociología',
                'short_name' => 'Sociología',
                'sunedu_category' => 'CIENCIAS SOCIALES Y DEL COMPORTAMIENTO',
                'category_group' => 'Ciencias Sociales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en sociología',
                'display_order' => 101,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ANTROPOLOGIA',
                'name' => 'Antropología',
                'short_name' => 'Antropología',
                'sunedu_category' => 'CIENCIAS SOCIALES Y DEL COMPORTAMIENTO',
                'category_group' => 'Ciencias Sociales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en antropología',
                'display_order' => 102,
                'is_active' => true,
            ],

            // ========== Comunicación ==========
            [
                'code' => 'CAR_CIENCIAS_COMUNICACION',
                'name' => 'Ciencias de la Comunicación',
                'short_name' => 'Comunicación',
                'sunedu_category' => 'PERIODISMO E INFORMACIÓN',
                'category_group' => 'Comunicación',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en ciencias de la comunicación',
                'display_order' => 110,
                'is_active' => true,
            ],

            // ========== Turismo ==========
            [
                'code' => 'CAR_TURISMO',
                'name' => 'Turismo',
                'short_name' => 'Turismo',
                'sunedu_category' => 'SERVICIOS PERSONALES',
                'category_group' => 'Turismo y Hotelería',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en turismo y hotelería',
                'display_order' => 120,
                'is_active' => true,
            ],

            // ========== Ciencias Naturales ==========
            [
                'code' => 'CAR_BIOLOGIA',
                'name' => 'Biología',
                'short_name' => 'Biología',
                'sunedu_category' => 'CIENCIAS BIOLÓGICAS Y AFINES',
                'category_group' => 'Ciencias Naturales',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en biología',
                'display_order' => 130,
                'is_active' => true,
            ],

            // ========== Ciencias Agrarias ==========
            [
                'code' => 'CAR_AGRONOMIA',
                'name' => 'Agronomía',
                'short_name' => 'Agronomía',
                'sunedu_category' => 'AGRICULTURA',
                'category_group' => 'Ciencias Agrarias',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en agronomía',
                'display_order' => 140,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_AGROINDUSTRIAL',
                'name' => 'Ingeniería Agroindustrial',
                'short_name' => 'Ing. Agroindustrial',
                'sunedu_category' => 'INDUSTRIA Y PRODUCCIÓN',
                'category_group' => 'Ciencias Agrarias',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería agroindustrial',
                'display_order' => 141,
                'is_active' => true,
            ],

            // ========== Artes ==========
            [
                'code' => 'CAR_ARTE',
                'name' => 'Arte',
                'short_name' => 'Arte',
                'sunedu_category' => 'ARTE',
                'category_group' => 'Artes',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en arte',
                'display_order' => 150,
                'is_active' => true,
            ],

            // ========== Humanidades ==========
            [
                'code' => 'CAR_HISTORIA',
                'name' => 'Historia',
                'short_name' => 'Historia',
                'sunedu_category' => 'HUMANIDADES',
                'category_group' => 'Humanidades',
                'requires_colegiatura' => false,
                'description' => 'Carrera profesional en historia',
                'display_order' => 160,
                'is_active' => true,
            ],

            // ========== Carreras Técnicas ==========
            [
                'code' => 'CAR_TECNICO_AGROPECUARIO',
                'name' => 'Técnico Agropecuario',
                'short_name' => 'Téc. Agropecuario',
                'sunedu_category' => 'AGRICULTURA',
                'category_group' => 'Técnico',
                'requires_colegiatura' => false,
                'description' => 'Carrera técnica en producción agropecuaria',
                'display_order' => 200,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_SEGURIDAD_INDUSTRIAL',
                'name' => 'Seguridad Industrial y Prevención de Riesgos',
                'short_name' => 'Seg. Industrial',
                'sunedu_category' => 'SERVICIOS DE HIGIENE Y SALUD OCUPACIONAL',
                'category_group' => 'Técnico',
                'requires_colegiatura' => false,
                'description' => 'Carrera técnica en seguridad industrial',
                'display_order' => 201,
                'is_active' => true,
            ],

            // ========== Otras Ingenierías ==========
            [
                'code' => 'CAR_ING_ELECTRONICA',
                'name' => 'Ingeniería Electrónica',
                'short_name' => 'Ing. Electrónica',
                'sunedu_category' => 'INGENIERÍA Y PROFESIONES AFINES',
                'category_group' => 'Ingeniería Electrónica',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería electrónica',
                'display_order' => 43,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_ELECTRICA',
                'name' => 'Ingeniería Eléctrica',
                'short_name' => 'Ing. Eléctrica',
                'sunedu_category' => 'INGENIERÍA Y PROFESIONES AFINES',
                'category_group' => 'Ingeniería Eléctrica',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería eléctrica',
                'display_order' => 44,
                'is_active' => true,
            ],
            [
                'code' => 'CAR_ING_QUIMICA',
                'name' => 'Ingeniería Química',
                'short_name' => 'Ing. Química',
                'sunedu_category' => 'INDUSTRIA Y PRODUCCIÓN',
                'category_group' => 'Ingeniería Química',
                'requires_colegiatura' => true,
                'description' => 'Carrera profesional en ingeniería química',
                'display_order' => 45,
                'is_active' => true,
            ],
        ];
    }
}
