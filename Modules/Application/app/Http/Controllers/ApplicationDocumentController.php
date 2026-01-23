<?php

namespace Modules\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Application\Services\ApplicationService;

class ApplicationDocumentController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Download application document
     *
     * @param string $documentId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $documentId)
    {
        return $this->applicationService->downloadDocument($documentId);
    }
}
