<?php

namespace Modules\Evaluation\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Evaluation\Entities\{Evaluation, EvaluatorAssignment, EvaluationCriterion};
use Modules\Evaluation\Policies\{
    EvaluationPolicy,
    EvaluatorAssignmentPolicy,
    EvaluationCriterionPolicy,
    AutomaticEvaluationPolicy
};

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Evaluation::class => EvaluationPolicy::class,
        EvaluatorAssignment::class => EvaluatorAssignmentPolicy::class,
        EvaluationCriterion::class => EvaluationCriterionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}