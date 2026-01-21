<?php

namespace App\Dto\Invoice\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class InvoiceItemRequestDto
{
    #[Assert\NotBlank(message: 'El ID del servicio es obligatorio.')]
    #[Assert\Type(type: 'numeric', message: 'ID de servicio inválido.')]
    public int $serviceId;
    #[Assert\Positive(message: 'La cantidad tiene que ser un número positivo.')]
    public int $quantity;
}