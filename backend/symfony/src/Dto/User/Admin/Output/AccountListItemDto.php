<?php

namespace App\Dto\User\Admin\Output;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class AccountListItemDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $dni,
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public ?string $city,
        public ?string $zipCode,
        public string $role,
        public bool $isActive,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public \DateTimeImmutable $createdAt,
    ) {}
}
