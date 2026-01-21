<?php

namespace App\Dto\Invoice\Request;

use App\Enum\InvoicePaymentMethod;
use Symfony\Component\Validator\Constraints as Assert;

class PayInvoiceRequestDto
{
    #[Assert\NotNull(message: 'El método de pago es obligatorio.')]
    public InvoicePaymentMethod $paymentMethod;

    #[Assert\Date(message: 'Formato de fecha inválido (YYYY-MM-DD)')]
    public ?string $paymentDate = null;
}