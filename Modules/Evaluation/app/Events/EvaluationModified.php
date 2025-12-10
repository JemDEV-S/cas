<?php

namespace Modules\Evaluation\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Evaluation\Entities\Evaluation;

class EvaluationModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Evaluation $evaluation;
    public string $reason;
    public int $modifiedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Evaluation $evaluation, string $reason, int $modifiedBy)
    {
        $this->evaluation = $evaluation;
        $this->reason = $reason;
        $this->modifiedBy = $modifiedBy;
    }
}