<?php

namespace App\Dto\Pet;

use Symfony\Component\Validator\Constraints as Assert;

class PetCreateRequestDto
{
    #[Assert\NotBlank(message: 'El cliente es obligatorio.')]
    #[Assert\Type(type: 'numeric', message: 'ID de cliente inválido.')]
    public int $clientId;

    #[Assert\NotBlank(message: 'El nombre de la mascota es obligatorio.')]
    #[Assert\Length(
        min:2,
        max:100,
        minMessage: 'El nombre debe tener al menos {{ limit }} caracteres.', maxMessage: 'El nombre no puede exceder {{ limit }} caracteres.'
    )]
    public string $name;

    #[Assert\NotBlank(message: 'El tipo de mascota es obligatorio.')]
    #[Assert\Type(type: 'numeric', message: 'ID de tipo de mascota invalido.')]
    public int $petTypeId;

    #[Assert\Length(
        max:100,
        maxMessage: 'La raza no puede exceder de {{ limit }} caracteres.'
    )]
    public ?string $breed = null;

    #[Assert\Date(message: 'Formato fecha inválido (YYYY-MM-DD)')]
    public ?string $birthDate = null;

    #[Assert\NotBlank(message: 'Género obligatorio')]
    #[Assert\Choice(
        choices: ['macho', 'hembra'],
        message: 'Género debe ser macho o hembra'
    )]
    public string $gender;

    #[Assert\Length(
        max:50,
        maxMessage: 'El color no puede exceder de {{ limit }} caracteres.'
    )]
    public ?string $color = null;

    #[Assert\Length(
        max:15,
        maxMessage: 'El microchip no puede exceder de {{ limit }} caracteres.'
    )]
    public ?string $microchip = null;

    #[Assert\Positive(message: 'El peso debe ser un número positivo.')]
    public ?float $weight = null;

    public ?string $allergies = null;

    #[Assert\Type('boolean', message: 'El valor de esterilizado debe ser verdadero o falso.')]
    public ?bool $sterilized = null;

    #[Assert\Length(max: 100, maxMessage: 'Aseguradora máximo {{ limit }} caracteres')]
    public ?string $insuranceProvider = null;

    #[Assert\Length(max: 100, maxMessage: 'Número póliza máximo {{ limit }} caracteres')]
    public ?string $insurancePolicyNumber = null;

    public ?string $notes = null;
}