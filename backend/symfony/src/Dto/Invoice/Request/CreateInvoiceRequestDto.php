<?php

namespace App\Dto\Invoice\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateInvoiceRequestDto
{
    #[Assert\NotBlank(message: 'El ID del usuario es obligatorio.')]
    #[Assert\Type(type: 'numeric', message: 'ID de cliente inválido.')]
    public int $userId;

    #[Assert\NotBlank(message: 'El ID de la mascota es obligatorio.')]
    #[Assert\Type(type: 'numeric', message: 'ID de mascota inválido.')]
    public int $petId;

    #[Assert\NotBlank(message: 'La fecha de factura es obligatoria.')]
    #[Assert\Date(message: 'Formato fecha inválido (YYYY-MM-DD)')]
    public string $invoiceDate;
    public ?string $notes = null;

    /** @var InvoiceItemRequestDto[] */
    public array $invoiceItems = [];
}