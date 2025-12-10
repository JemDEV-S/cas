<?php

namespace Modules\Application\Enums;

enum ApplicationStatus: string
{
    case SUBMITTED = 'PRESENTADA';
    case IN_REVIEW = 'EN_REVISION';
    case ELIGIBLE = 'APTO';
    case NOT_ELIGIBLE = 'NO_APTO';
    case IN_EVALUATION = 'EN_EVALUACION';
    case AMENDMENT_REQUIRED = 'SUBSANACION';
    case APPROVED = 'APROBADA';
    case REJECTED = 'RECHAZADA';
    case WITHDRAWN = 'DESISTIDA';

    public function label(): string
    {
        return match($this) {
            self::SUBMITTED => 'Presentada',
            self::IN_REVIEW => 'En Revisión',
            self::ELIGIBLE => 'Apto',
            self::NOT_ELIGIBLE => 'No Apto',
            self::IN_EVALUATION => 'En Evaluación',
            self::AMENDMENT_REQUIRED => 'Subsanación Requerida',
            self::APPROVED => 'Aprobada',
            self::REJECTED => 'Rechazada',
            self::WITHDRAWN => 'Desistida',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SUBMITTED => 'blue',
            self::IN_REVIEW => 'yellow',
            self::ELIGIBLE => 'green',
            self::NOT_ELIGIBLE => 'red',
            self::IN_EVALUATION => 'purple',
            self::AMENDMENT_REQUIRED => 'orange',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::WITHDRAWN => 'gray',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::SUBMITTED => in_array($status, [
                self::IN_REVIEW,
                self::WITHDRAWN
            ]),
            self::IN_REVIEW => in_array($status, [
                self::ELIGIBLE,
                self::NOT_ELIGIBLE,
                self::AMENDMENT_REQUIRED,
                self::WITHDRAWN
            ]),
            self::AMENDMENT_REQUIRED => in_array($status, [
                self::IN_REVIEW,
                self::NOT_ELIGIBLE,
                self::WITHDRAWN
            ]),
            self::ELIGIBLE => in_array($status, [
                self::IN_EVALUATION,
                self::WITHDRAWN
            ]),
            self::IN_EVALUATION => in_array($status, [
                self::APPROVED,
                self::REJECTED
            ]),
            default => false,
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [
            self::SUBMITTED,
            self::IN_REVIEW,
            self::AMENDMENT_REQUIRED
        ]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::NOT_ELIGIBLE,
            self::APPROVED,
            self::REJECTED,
            self::WITHDRAWN
        ]);
    }
}
