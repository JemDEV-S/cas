<?php

namespace Modules\JobProfile\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Entities\PositionCode;
use Modules\JobProfile\Repositories\Contracts\PositionCodeRepositoryInterface;
use Modules\JobProfile\Services\Contracts\PositionCodeServiceInterface;

class PositionCodeService implements PositionCodeServiceInterface
{
    public function __construct(
        protected PositionCodeRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getActive(): Collection
    {
        return $this->repository->findActive();
    }

    public function findById(string $id): ?PositionCode
    {
        return $this->repository->findById($id);
    }

    public function findByCode(string $code): ?PositionCode
    {
        return $this->repository->findByCode($code);
    }

    public function create(array $data): PositionCode
    {
        return DB::transaction(function () use ($data) {
            // Validar que el código no exista
            if (isset($data['code']) && $this->repository->findByCode($data['code'])) {
                throw new BusinessRuleException("El código '{$data['code']}' ya existe.");
            }

            // Validar salario base
            if (!isset($data['base_salary']) || $data['base_salary'] <= 0) {
                throw new BusinessRuleException('El salario base debe ser mayor a cero.');
            }

            // Validar porcentaje de EsSalud
            if (isset($data['essalud_percentage'])) {
                if ($data['essalud_percentage'] < 0 || $data['essalud_percentage'] > 100) {
                    throw new BusinessRuleException('El porcentaje de EsSalud debe estar entre 0 y 100.');
                }
            }

            // Validar meses de contrato
            if (isset($data['contract_months']) && $data['contract_months'] < 1) {
                throw new BusinessRuleException('La duración del contrato debe ser al menos 1 mes.');
            }

            $positionCode = $this->repository->create($data);

            // Validar cálculos automáticos
            $this->validateCalculations($positionCode);

            return $positionCode;
        });
    }

    public function update(string $id, array $data): PositionCode
    {
        return DB::transaction(function () use ($id, $data) {
            $positionCode = $this->repository->findById($id);

            if (!$positionCode) {
                throw new BusinessRuleException('Código de posición no encontrado.');
            }

            // Validar código si se está cambiando
            if (isset($data['code']) && $data['code'] !== $positionCode->code) {
                if ($this->repository->findByCode($data['code'])) {
                    throw new BusinessRuleException("El código '{$data['code']}' ya existe.");
                }
            }

            // Validar salario base
            if (isset($data['base_salary']) && $data['base_salary'] <= 0) {
                throw new BusinessRuleException('El salario base debe ser mayor a cero.');
            }

            // Validar porcentaje de EsSalud
            if (isset($data['essalud_percentage'])) {
                if ($data['essalud_percentage'] < 0 || $data['essalud_percentage'] > 100) {
                    throw new BusinessRuleException('El porcentaje de EsSalud debe estar entre 0 y 100.');
                }
            }

            // Validar meses de contrato
            if (isset($data['contract_months']) && $data['contract_months'] < 1) {
                throw new BusinessRuleException('La duración del contrato debe ser al menos 1 mes.');
            }

            // Verificar si tiene perfiles asociados activos antes de modificar valores monetarios
            if (isset($data['base_salary']) || isset($data['essalud_percentage']) || isset($data['contract_months'])) {
                $activeProfilesCount = $positionCode->jobProfiles()
                    ->whereIn('status', ['approved', 'active'])
                    ->count();

                if ($activeProfilesCount > 0) {
                    throw new BusinessRuleException(
                        'No se pueden modificar los valores monetarios porque hay perfiles aprobados o activos asociados a este código.'
                    );
                }
            }

            $updated = $this->repository->update($id, $data);

            // Validar cálculos automáticos
            $this->validateCalculations($updated);

            return $updated;
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $positionCode = $this->repository->findById($id);

            if (!$positionCode) {
                throw new BusinessRuleException('Código de posición no encontrado.');
            }

            // Verificar si tiene perfiles asociados
            $profilesCount = $positionCode->jobProfiles()->count();
            if ($profilesCount > 0) {
                throw new BusinessRuleException(
                    "No se puede eliminar el código porque tiene {$profilesCount} perfil(es) asociado(s)."
                );
            }

            return $this->repository->delete($id);
        });
    }

    public function activate(string $id): PositionCode
    {
        $positionCode = $this->repository->findById($id);

        if (!$positionCode) {
            throw new BusinessRuleException('Código de posición no encontrado.');
        }

        if ($positionCode->isActive()) {
            throw new BusinessRuleException('El código de posición ya está activo.');
        }

        $this->repository->activate($id);

        return $this->repository->findById($id);
    }

    public function deactivate(string $id): PositionCode
    {
        $positionCode = $this->repository->findById($id);

        if (!$positionCode) {
            throw new BusinessRuleException('Código de posición no encontrado.');
        }

        if (!$positionCode->isActive()) {
            throw new BusinessRuleException('El código de posición ya está inactivo.');
        }

        // Verificar si tiene perfiles activos asociados
        $activeProfilesCount = $positionCode->jobProfiles()
            ->where('status', 'active')
            ->count();

        if ($activeProfilesCount > 0) {
            throw new BusinessRuleException(
                "No se puede desactivar el código porque tiene {$activeProfilesCount} perfil(es) activo(s) asociado(s)."
            );
        }

        $this->repository->deactivate($id);

        return $this->repository->findById($id);
    }

    /**
     * Valida que los cálculos automáticos sean correctos
     */
    public function validateCalculations(PositionCode $positionCode): bool
    {
        $expectedEssalud = $positionCode->calculateEssalud();
        $expectedMonthly = $positionCode->calculateMonthlyTotal();
        $expectedQuarterly = $positionCode->calculateQuarterlyTotal();

        // Refrescar para obtener los valores calculados de la BD
        $positionCode->refresh();

        $tolerance = 0.01; // Tolerancia de 1 centavo por redondeo

        if (abs($positionCode->essalud_amount - $expectedEssalud) > $tolerance) {
            throw new BusinessRuleException(
                'Error en cálculo de EsSalud. Esperado: ' . $expectedEssalud . ', Calculado: ' . $positionCode->essalud_amount
            );
        }

        if (abs($positionCode->monthly_total - $expectedMonthly) > $tolerance) {
            throw new BusinessRuleException(
                'Error en cálculo de total mensual. Esperado: ' . $expectedMonthly . ', Calculado: ' . $positionCode->monthly_total
            );
        }

        if (abs($positionCode->quarterly_total - $expectedQuarterly) > $tolerance) {
            throw new BusinessRuleException(
                'Error en cálculo de total trimestral. Esperado: ' . $expectedQuarterly . ', Calculado: ' . $positionCode->quarterly_total
            );
        }

        return true;
    }
}
