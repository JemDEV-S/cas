<?php

namespace Modules\JobProfile\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\JobProfile\Entities\JobProfile;

class JobProfileRepository extends BaseRepository
{
    public function __construct(JobProfile $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?JobProfile
    {
        return $this->model->where('code', $code)->first();
    }

    public function getByStatus(string $status)
    {
        return $this->model->byStatus($status)->with(['organizationalUnit', 'requirements', 'responsibilities'])->get();
    }

    public function getApproved()
    {
        return $this->model->approved()->with(['organizationalUnit'])->get();
    }

    public function getActive()
    {
        return $this->model->active()->with(['organizationalUnit'])->get();
    }

    public function getByOrganizationalUnit(string $unitId)
    {
        return $this->model->where('organizational_unit_id', $unitId)->get();
    }
}
