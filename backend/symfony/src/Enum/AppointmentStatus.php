<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case Requested = 'solicitada';
    case Confirmed = 'confirmada';
    case Completed = 'completada';
    case Cancelled = 'cancelada';
}
