<?php

namespace Modules\Document\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DigitalSignature;

class SignatureRejected
{
    use Dispatchable, SerializesModels;

    public GeneratedDocument $document;
    public DigitalSignature $signature;
    public string $reason;

    public function __construct(GeneratedDocument $document, DigitalSignature $signature, string $reason)
    {
        $this->document = $document;
        $this->signature = $signature;
        $this->reason = $reason;
    }
}
