<?php

namespace Modules\Application\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class InvalidCareerIdsMultiSheetExport implements WithMultipleSheets
{
    protected Collection $invalidRecords;
    protected Collection $validCareers;

    public function __construct(Collection $invalidRecords, Collection $validCareers)
    {
        $this->invalidRecords = $invalidRecords;
        $this->validCareers = $validCareers;
    }

    public function sheets(): array
    {
        return [
            new InvalidCareerIdsExport($this->invalidRecords),
            new ValidCareersReferenceExport($this->validCareers),
        ];
    }
}
