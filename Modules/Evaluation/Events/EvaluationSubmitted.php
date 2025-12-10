<?php

namespace Modules\Evaluation\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Evaluation\Entities\Evaluation;

class EvaluationSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Evaluation $evaluation;

    /**
     * Create a new event instance.
     */
    public function __construct(Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }
}