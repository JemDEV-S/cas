<?php

namespace Modules\Document\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DigitalSignature;

class DocumentSigned
{
    use Dispatchable, SerializesModels;

    public GeneratedDocument $document;
    public DigitalSignature $signature;

    public function __construct(GeneratedDocument $document, DigitalSignature $signature)
    {
        $this->document = $document;
        $this->signature = $signature;
    }
}
