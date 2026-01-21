<?php

namespace App\Mapper;

use App\Dto\User\Profile\Input\UpdateProfileInputDto;
use App\Dto\User\Profile\Output\UserBasicDto;
use App\Entity\User;

class ProfileMapper
{
    // ====================
    // INPUT
    // ====================
    public function fromUpdateDto(User $user, UpdateProfileInputDto $dto): User
    {
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setPhone($dto->phone);
        $user->setAddress($dto->address);
        $user->setCity($dto->city);
        $user->setZipCode($dto->zipCode);
        $user->setCountry($dto->country);

        return $user;
    }

    // ====================
    // OUTPUT
    // ====================

    public function toBasic(User $user): UserBasicDto
    {
        return new UserBasicDto(
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getCity(),
            $user->getZipCode(),
            $user->getCountry(),
            $user->getAddress(),
        );
    }
}