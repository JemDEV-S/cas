<?php

namespace Modules\Document\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\GeneratedDocument;

class DocumentReadyForSignature
{
    use Dispatchable, SerializesModels;

    public GeneratedDocument $document;
    public string $signerId;

    public function __construct(GeneratedDocument $document, string $signerId)
    {
        $this->document = $document;
        $this->signerId = $signerId;
    }
}
