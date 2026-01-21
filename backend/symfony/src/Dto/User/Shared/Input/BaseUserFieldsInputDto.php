<?php

namespace App\Dto\User\Shared\Input;

use Symfony\Component\Validator\Constraints as Assert;

class BaseUserFieldsInputDto
{
    #[Assert\Email(message: "El email '{{ value }}' no es válido.")]
    #[Assert\Length(
        min: 3,
        max: 180,
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $email = null;

    #[Assert\Length(
        min: 8,
        max: 20,
        minMessage: "Debe tener al menos {{ limit }} caracteres.",
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9]+$/",
        message: "El DNI debe contener solo letras mayúsculas y números, sin espacios ni guiones."
    )]
    public ?string $dni = null;

    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Debe tener más al menos {{ limit }} caracteres.",
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $firstName = null;

    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: "Debe tener más al menos {{ limit }} caracteres.",
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $lastName = null;

    #[Assert\Length(max: 20)]
    #[Assert\Regex(
        pattern: "/^(\+34|0034)?[6-9][0-9]{8}$/",
        message: "Debe ser un número español válido, sin espacios ni guiones."
    )]
    public ?string $phone = null;

    #[Assert\Length(
        max: 150,
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $address = null;

    #[Assert\Length(
        max: 50,
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $city = null;

    #[Assert\Length(
        max: 10,
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $zipCode = null;

    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Debe tener más al menos {{ limit }} caracteres.",
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $country = null;

    #[Assert\Length(
        max: 1000,
        maxMessage: "No puede tener más de {{ limit }} caracteres."
    )]
    public ?string $additionalNotes = null;
}