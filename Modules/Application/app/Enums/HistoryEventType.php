<?php

namespace Modules\Application\Enums;

enum HistoryEventType: string
{
    case CREATED = 'CREATED';
    case UPDATED = 'UPDATED';
    case STATUS_CHANGED = 'STATUS_CHANGED';
    case DOCUMENT_UPLOADED = 'DOCUMENT_UPLOADED';
    case DOCUMENT_DELETED = 'DOCUMENT_DELETED';
    case DOCUMENT_VERIFIED = 'DOCUMENT_VERIFIED';
    case EVALUATED = 'EVALUATED';
    case COMMENTED = 'COMMENTED';
    case AMENDMENT_REQUESTED = 'AMENDMENT_REQUESTED';
    case WITHDRAWN = 'WITHDRAWN';
    case ELIGIBILITY_CHECKED = 'ELIGIBILITY_CHECKED';

    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Creación',
            self::UPDATED => 'Actualización',
            self::STATUS_CHANGED => 'Cambio de Estado',
            self::DOCUMENT_UPLOADED => 'Documento Subido',
            self::DOCUMENT_DELETED => 'Documento Eliminado',
            self::DOCUMENT_VERIFIED => 'Documento Verificado',
            self::EVALUATED => 'Evaluación',
            self::COMMENTED => 'Comentario',
            self::AMENDMENT_REQUESTED => 'Subsanación Solicitada',
            self::WITHDRAWN => 'Desistimiento',
            self::ELIGIBILITY_CHECKED => 'Verificación de Elegibilidad',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CREATED => 'plus-circle',
            self::UPDATED => 'edit',
            self::STATUS_CHANGED => 'arrow-right',
            self::DOCUMENT_UPLOADED => 'upload',
            self::DOCUMENT_DELETED => 'trash',
            self::DOCUMENT_VERIFIED => 'check-circle',
            self::EVALUATED => 'star',
            self::COMMENTED => 'message-square',
            self::AMENDMENT_REQUESTED => 'alert-circle',
            self::WITHDRAWN => 'x-circle',
            self::ELIGIBILITY_CHECKED => 'clipboard-check',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CREATED => 'green',
            self::UPDATED => 'blue',
            self::STATUS_CHANGED => 'purple',
            self::DOCUMENT_UPLOADED => 'indigo',
            self::DOCUMENT_DELETED => 'red',
            self::DOCUMENT_VERIFIED => 'green',
            self::EVALUATED => 'yellow',
            self::COMMENTED => 'gray',
            self::AMENDMENT_REQUESTED => 'orange',
            self::WITHDRAWN => 'red',
            self::ELIGIBILITY_CHECKED => 'teal',
        };
    }
}
