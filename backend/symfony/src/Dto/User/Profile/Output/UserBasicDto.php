<?php

namespace App\Dto\User\Profile\Output;

class UserBasicDto
{
    public function __construct(
        public string $email,
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public ?string $city,
        public ?string $zipCode,
        public ?string $country,
        public ?string $address,
    ) {}
}