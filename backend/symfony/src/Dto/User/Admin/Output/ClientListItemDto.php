<?php

namespace App\Dto\User\Admin\Output;

class ClientListItemDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $dni,
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $zipCode,
        public ?string $additionalNotes,
        public int $petCount,
        public string $role,
    ) {}
}
