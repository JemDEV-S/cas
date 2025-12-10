<?php

namespace Modules\Evaluation\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Evaluation\Entities\EvaluatorAssignment;

class EvaluationAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public EvaluatorAssignment $assignment;

    /**
     * Create a new event instance.
     */
    public function __construct(EvaluatorAssignment $assignment)
    {
        $this->assignment = $assignment;
    }
}