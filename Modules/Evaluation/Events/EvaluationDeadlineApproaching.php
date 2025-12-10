<?php

namespace Modules\Evaluation\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Evaluation\Entities\Evaluation;

class EvaluationDeadlineApproaching
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Evaluation $evaluation;
    public int $daysRemaining;

    /**
     * Create a new event instance.
     */
    public function __construct(Evaluation $evaluation, int $daysRemaining)
    {
        $this->evaluation = $evaluation;
        $this->daysRemaining = $daysRemaining;
    }
}