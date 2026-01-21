<?php

namespace App\Enum;

enum InvoicePaymentMethod: string
{
    case Cash = 'metálico';
    case CreditCard = 'tarjeta de crédito';
    case BankTransfer = 'transferencia';
    case Other = 'otro';
}
