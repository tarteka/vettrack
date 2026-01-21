<?php

namespace App\Mapper;

use App\Dto\Pet\PetSummaryDto;
use App\Dto\User\Admin\Input\CreateUserInputDto;
use App\Dto\User\Admin\Input\UpdateUserInputDto;
use App\Dto\User\Admin\Output\AccountListItemDto;
use App\Dto\User\Admin\Output\ClientListItemDto;
use App\Dto\User\Admin\Output\UserBasicDto;
use App\Dto\User\Admin\Output\UserDetailDto;
use App\Entity\User;

class UserAdminMapper
{

    // ====================
    // INPUT
    // ====================

    public function fromCreateDto(CreateUserInputDto $dto): User
    {
        $user = new User();

        $user->setEmail($dto->email)
            ->setDni($dto->dni)
            ->setFirstName($dto->firstName)
            ->setLastName($dto->lastName)
            ->setPhone($dto->phone)
            ->setAddress($dto->address)
            ->setCity($dto->city)
            ->setZipCode($dto->zipCode)
            ->setCountry($dto->country)
            ->setAdditionalNotes($dto->additionalNotes)
            ->setRoles($dto->roles)
            ->setLicenseNumber($dto->licenseNumber)
            ->setSpecialization($dto->specialization)
            ->setIsActive(true)
        ;

        return $user;
    }

    public function fromUpdateDto(User $user, UpdateUserInputDto $dto): User
    {
        if ($dto->email !== null) $user->setEmail($dto->email);
        if ($dto->dni !== null) $user->setDni($dto->dni);
        if ($dto->firstName !== null) $user->setFirstName($dto->firstName);
        if ($dto->lastName !== null) $user->setLastName($dto->lastName);
        if ($dto->phone !== null) $user->setPhone($dto->phone);
        if ($dto->address !== null) $user->setAddress($dto->address);
        if ($dto->city !== null) $user->setCity($dto->city);
        if ($dto->zipCode !== null) $user->setZipCode($dto->zipCode);
        if ($dto->country !== null) $user->setCountry($dto->country);
        if ($dto->additionalNotes !== null) $user->setAdditionalNotes($dto->additionalNotes);
        if ($dto->licenseNumber !== null) $user->setLicenseNumber($dto->licenseNumber);
        if ($dto->specialization !== null) $user->setSpecialization($dto->specialization);

        return $user;
    }

    // ====================
    // OUTPUT
    // ====================

    /**
     * @param PetSummaryDto[] $petDtos
     */
    public function toDetail(User $user, array $petDtos): UserDetailDto
    {
        return new UserDetailDto(
            $user->getId(),
            $user->getEmail(),
            $user->getDni(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getCity(),
            $user->getZipCode(),
            $user->getAddress(),
            $petDtos,
            count($petDtos),
            $user->getHighestRole(),
            $user->getCreatedAt()
        );
    }

    public function toClientList(User $user, int $petCount): ClientListItemDto
    {
        return new ClientListItemDto(
            $user->getId(),
            $user->getEmail(),
            $user->getDni(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getAddress(),
            $user->getCity(),
            $user->getZipCode(),
            $user->getAdditionalNotes(),
            $petCount,
            $user->getHighestRole(),
        );
    }

    public function toAccountList(User $user): AccountListItemDto
    {
        return new AccountListItemDto(
            $user->getId(),
            $user->getEmail(),
            $user->getDni(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getCity(),
            $user->getZipCode(),
            $user->getHighestRole(),
            $user->isActive(),
            $user->getCreatedAt()
        );
    }

    public function toBasic(User $user): UserBasicDto
    {
        return new UserBasicDto(
            $user->getId(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPhone(),
            $user->getCity(),
            $user->getZipCode(),
            $user->getAddress(),
            $user->getDni(),
            $user->getHighestRole(),
            $user->isActive(),
        );
    }
}