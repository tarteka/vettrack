<?php

namespace App\Dto\Pet;

use App\Entity\User;

readonly class ClientResumeResponseDto
{
    public function __construct(
        private int $id,
        private string $fullName,
        private string $email,
        private ?string $phone,
        private string $dni
    ){}

    /**
     * Creamos un objeto de tipo DTO a partir de un objeto de tipo Entity
     * @param User $client
     * @return ClientResumeResponseDto
     */
    public static function fromEntity(User $client): self
    {
        return new self(
            id: $client->getId(),
            fullName: $client->getFullName(),
            email: $client->getEmail(),
            phone: $client->getPhone(),
            dni: $client->getDni()
        );
    }

    /**
     * Creamos un array con los datos del cliente para enviarlo al frontend
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'phone' => $this->phone,
            'dni' => $this->dni,
        ];
    }
}