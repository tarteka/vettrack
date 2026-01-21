<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case Pending = 'pendiente';
    case Paid = 'pagada';
    case Overdue = 'atrasada';
    case Cancelled = 'cancelada';
}
