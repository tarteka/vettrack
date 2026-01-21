<?php

namespace App\Dto\User\Admin\Output;

class UserBasicDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public ?string $city,
        public ?string $zipCode,
        public ?string $address,
        public string $dni,
        public string $role,
        public bool $isActive,
    ) {}
}
