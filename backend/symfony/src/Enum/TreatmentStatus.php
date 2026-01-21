<?php

namespace App\Enum;

enum TreatmentStatus: string
{
    case Active = 'activo';
    case Completed = 'completado';

    case Suspended = 'suspendido';
}
