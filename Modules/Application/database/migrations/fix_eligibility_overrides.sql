-- Eliminar la restricción de clave foránea
ALTER TABLE `eligibility_overrides` DROP FOREIGN KEY `eligibility_overrides_application_id_foreign`;

-- Eliminar el índice único
ALTER TABLE `eligibility_overrides` DROP INDEX `eligibility_overrides_application_id_unique`;

-- Recrear la clave foránea sin restricción de unicidad
ALTER TABLE `eligibility_overrides`
ADD CONSTRAINT `eligibility_overrides_application_id_foreign`
FOREIGN KEY (`application_id`)
REFERENCES `applications`(`id`)
ON DELETE CASCADE;

-- Agregar índice normal para rendimiento
ALTER TABLE `eligibility_overrides` ADD INDEX `eligibility_overrides_application_id_index` (`application_id`);
