<?php

namespace Modules\Configuration\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Entities\ConfigGroup;
use Modules\Configuration\Enums\ValueTypeEnum;
use Modules\Configuration\Enums\InputTypeEnum;

class SystemConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedGeneralConfigs();
        $this->seedProcessConfigs();
        $this->seedDocumentsConfigs();
        $this->seedNotificationsConfigs();
        $this->seedSecurityConfigs();
        $this->seedIntegrationsConfigs();
        $this->seedReportsConfigs();
        $this->seedAuditConfigs();
    }

    protected function seedGeneralConfigs(): void
    {
        $group = ConfigGroup::where('code', 'general')->first();

        $configs = [
            [
                'key' => 'SYSTEM_NAME',
                'value' => 'Sistema de Convocatorias CAS',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'Sistema de Convocatorias CAS',
                'description' => 'Nombre de la institución',
                'display_name' => 'Nombre del Sistema',
                'input_type' => InputTypeEnum::TEXT,
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'SYSTEM_LOGO',
                'value' => '/images/logo.png',
                'value_type' => ValueTypeEnum::FILE,
                'default_value' => '/images/logo.png',
                'description' => 'Logo de la institución',
                'display_name' => 'Logo del Sistema',
                'input_type' => InputTypeEnum::FILE,
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'SYSTEM_PRIMARY_COLOR',
                'value' => '#1e40af',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => '#1e40af',
                'description' => 'Color primario del sistema (hexadecimal)',
                'display_name' => 'Color Primario',
                'input_type' => InputTypeEnum::COLOR,
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'SYSTEM_TIMEZONE',
                'value' => 'America/Lima',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'America/Lima',
                'description' => 'Zona horaria del sistema',
                'display_name' => 'Zona Horaria',
                'input_type' => InputTypeEnum::SELECT,
                'options' => ['America/Lima', 'America/Mexico_City', 'America/Bogota', 'America/Santiago'],
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'SYSTEM_LOCALE',
                'value' => 'es',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'es',
                'description' => 'Idioma del sistema',
                'display_name' => 'Idioma',
                'input_type' => InputTypeEnum::SELECT,
                'options' => ['es', 'en'],
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 5,
            ],
            [
                'key' => 'CONTACT_EMAIL',
                'value' => 'contacto@institucion.gob.pe',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'contacto@institucion.gob.pe',
                'description' => 'Email de contacto',
                'display_name' => 'Email de Contacto',
                'input_type' => InputTypeEnum::TEXT,
                'validation_rules' => ['email'],
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 6,
            ],
            [
                'key' => 'CONTACT_PHONE',
                'value' => '(01) 123-4567',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => '(01) 123-4567',
                'description' => 'Teléfono de contacto',
                'display_name' => 'Teléfono',
                'input_type' => InputTypeEnum::TEXT,
                'is_public' => true,
                'is_editable' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedProcessConfigs(): void
    {
        $group = ConfigGroup::where('code', 'process')->first();

        $configs = [
            [
                'key' => 'DEFAULT_APPLICATION_DEADLINE_DAYS',
                'value' => '15',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '15',
                'description' => 'Días de plazo para postulaciones',
                'display_name' => 'Días de Plazo para Postulaciones',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 5,
                'max_value' => 60,
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'DEFAULT_AMENDMENT_DEADLINE_DAYS',
                'value' => '3',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '3',
                'description' => 'Días para subsanar documentos',
                'display_name' => 'Días para Subsanación',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 1,
                'max_value' => 10,
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'DEFAULT_APPEAL_DEADLINE_DAYS',
                'value' => '3',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '3',
                'description' => 'Días para presentar recursos',
                'display_name' => 'Días para Recursos',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 1,
                'max_value' => 10,
                'is_editable' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'MAX_APPLICATIONS_PER_USER',
                'value' => '5',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '5',
                'description' => 'Límite de postulaciones simultáneas por usuario',
                'display_name' => 'Máximo de Postulaciones por Usuario',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 1,
                'max_value' => 20,
                'is_editable' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'AUTO_GENERATE_JOB_CODE',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Generar automáticamente códigos de convocatoria',
                'display_name' => 'Generar Códigos Automáticamente',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'display_order' => 5,
            ],
            [
                'key' => 'JOB_CODE_PREFIX',
                'value' => 'CONV',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'CONV',
                'description' => 'Prefijo para códigos de convocatoria',
                'display_name' => 'Prefijo de Códigos',
                'input_type' => InputTypeEnum::TEXT,
                'is_editable' => true,
                'display_order' => 6,
            ],
            [
                'key' => 'JOB_PROFILE_CREATION_START_DATE',
                'value' => null,
                'value_type' => ValueTypeEnum::DATE,
                'default_value' => null,
                'description' => 'Fecha de inicio para creación de perfiles de puesto',
                'display_name' => 'Fecha Inicio Creación de Perfiles',
                'input_type' => InputTypeEnum::DATE,
                'is_editable' => true,
                'display_order' => 7,
                'help_text' => 'Los usuarios solo podrán crear perfiles a partir de esta fecha',
            ],
            [
                'key' => 'JOB_PROFILE_CREATION_END_DATE',
                'value' => null,
                'value_type' => ValueTypeEnum::DATE,
                'default_value' => null,
                'description' => 'Fecha de fin para creación de perfiles de puesto',
                'display_name' => 'Fecha Fin Creación de Perfiles',
                'input_type' => InputTypeEnum::DATE,
                'is_editable' => true,
                'display_order' => 8,
                'help_text' => 'Los usuarios solo podrán crear perfiles hasta esta fecha',
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedDocumentsConfigs(): void
    {
        $group = ConfigGroup::where('code', 'documents')->first();

        $configs = [
            [
                'key' => 'MAX_FILE_SIZE_MB',
                'value' => '10',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '10',
                'description' => 'Tamaño máximo de archivo en MB',
                'display_name' => 'Tamaño Máximo de Archivo (MB)',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 1,
                'max_value' => 50,
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'ALLOWED_DOCUMENT_TYPES',
                'value' => '["pdf","docx","jpg","png"]',
                'value_type' => ValueTypeEnum::JSON,
                'default_value' => '["pdf","docx","jpg","png"]',
                'description' => 'Tipos de archivo permitidos',
                'display_name' => 'Tipos de Archivo Permitidos',
                'input_type' => InputTypeEnum::TEXTAREA,
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'DOCUMENT_STORAGE_DRIVER',
                'value' => 'local',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'local',
                'description' => 'Driver de almacenamiento',
                'display_name' => 'Driver de Almacenamiento',
                'input_type' => InputTypeEnum::SELECT,
                'options' => ['local', 's3', 'azure'],
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'DOCUMENT_RETENTION_YEARS',
                'value' => '7',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '7',
                'description' => 'Años de retención de documentos',
                'display_name' => 'Años de Retención',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 1,
                'max_value' => 20,
                'is_editable' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'REQUIRE_DIGITAL_SIGNATURE',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Requerir firma digital en documentos oficiales',
                'display_name' => 'Requerir Firma Digital',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'display_order' => 5,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedNotificationsConfigs(): void
    {
        $group = ConfigGroup::where('code', 'notifications')->first();

        $configs = [
            [
                'key' => 'NOTIFICATIONS_ENABLED',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Activar sistema de notificaciones',
                'display_name' => 'Notificaciones Habilitadas',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'EMAIL_FROM_ADDRESS',
                'value' => 'noreply@institucion.gob.pe',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'noreply@institucion.gob.pe',
                'description' => 'Email remitente',
                'display_name' => 'Email Remitente',
                'input_type' => InputTypeEnum::TEXT,
                'validation_rules' => ['email'],
                'is_editable' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'EMAIL_FROM_NAME',
                'value' => 'Sistema CAS',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'Sistema CAS',
                'description' => 'Nombre del remitente',
                'display_name' => 'Nombre Remitente',
                'input_type' => InputTypeEnum::TEXT,
                'is_editable' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedSecurityConfigs(): void
    {
        $group = ConfigGroup::where('code', 'security')->first();

        $configs = [
            [
                'key' => 'SESSION_LIFETIME',
                'value' => '120',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '120',
                'description' => 'Duración de sesión en minutos',
                'display_name' => 'Duración de Sesión (minutos)',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 30,
                'max_value' => 480,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'PASSWORD_MIN_LENGTH',
                'value' => '8',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '8',
                'description' => 'Longitud mínima de contraseña',
                'display_name' => 'Longitud Mínima de Contraseña',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 6,
                'max_value' => 32,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'PASSWORD_REQUIRE_UPPERCASE',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Requerir mayúsculas en contraseña',
                'display_name' => 'Requerir Mayúsculas',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 3,
            ],
            [
                'key' => 'PASSWORD_REQUIRE_NUMBERS',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Requerir números en contraseña',
                'display_name' => 'Requerir Números',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 4,
            ],
            [
                'key' => 'TWO_FACTOR_ENABLED',
                'value' => '0',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '0',
                'description' => 'Habilitar autenticación de dos factores',
                'display_name' => '2FA Habilitado',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 5,
            ],
            [
                'key' => 'MAX_LOGIN_ATTEMPTS',
                'value' => '5',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '5',
                'description' => 'Intentos máximos de login',
                'display_name' => 'Intentos Máximos de Login',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 3,
                'max_value' => 10,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 6,
            ],
            [
                'key' => 'LOCKOUT_DURATION_MINUTES',
                'value' => '15',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '15',
                'description' => 'Duración de bloqueo en minutos',
                'display_name' => 'Duración de Bloqueo (minutos)',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 5,
                'max_value' => 60,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedIntegrationsConfigs(): void
    {
        $group = ConfigGroup::where('code', 'integrations')->first();

        $configs = [
            [
                'key' => 'RENIEC_API_ENABLED',
                'value' => '0',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '0',
                'description' => 'Habilitar integración con RENIEC',
                'display_name' => 'Integración RENIEC',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'SUNAT_API_ENABLED',
                'value' => '0',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '0',
                'description' => 'Habilitar integración con SUNAT',
                'display_name' => 'Integración SUNAT',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 2,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedReportsConfigs(): void
    {
        $group = ConfigGroup::where('code', 'reports')->first();

        $configs = [
            [
                'key' => 'DEFAULT_REPORT_FORMAT',
                'value' => 'pdf',
                'value_type' => ValueTypeEnum::STRING,
                'default_value' => 'pdf',
                'description' => 'Formato de reporte por defecto',
                'display_name' => 'Formato de Reporte',
                'input_type' => InputTypeEnum::SELECT,
                'options' => ['pdf', 'excel', 'csv'],
                'is_editable' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'CACHE_REPORTS',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Cachear reportes generados',
                'display_name' => 'Cachear Reportes',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'display_order' => 2,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }

    protected function seedAuditConfigs(): void
    {
        $group = ConfigGroup::where('code', 'audit')->first();

        $configs = [
            [
                'key' => 'AUDIT_ENABLED',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Habilitar sistema de auditoría',
                'display_name' => 'Auditoría Habilitada',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 1,
            ],
            [
                'key' => 'AUDIT_RETENTION_DAYS',
                'value' => '2555',
                'value_type' => ValueTypeEnum::INTEGER,
                'default_value' => '2555',
                'description' => 'Días de retención de logs (7 años)',
                'display_name' => 'Retención de Logs (días)',
                'input_type' => InputTypeEnum::NUMBER,
                'min_value' => 365,
                'max_value' => 3650,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'LOG_FAILED_LOGINS',
                'value' => '1',
                'value_type' => ValueTypeEnum::BOOLEAN,
                'default_value' => '1',
                'description' => 'Registrar intentos de login fallidos',
                'display_name' => 'Registrar Logins Fallidos',
                'input_type' => InputTypeEnum::BOOLEAN,
                'is_editable' => true,
                'is_system' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::create(array_merge($config, ['config_group_id' => $group->id]));
        }
    }
}
