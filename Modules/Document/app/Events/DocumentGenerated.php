<?php

namespace Modules\Document\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\GeneratedDocument;

class DocumentGenerated
{
    use Dispatchable, SerializesModels;

    public GeneratedDocument $document;
    public string $generatedBy;

    public function __construct(GeneratedDocument $document, string $generatedBy)
    {
        $this->document = $document;
        $this->generatedBy = $generatedBy;
    }
}
