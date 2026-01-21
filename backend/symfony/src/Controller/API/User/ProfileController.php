<?php

namespace App\Controller\API\User;

use App\Dto\User\Profile\Input\UpdatePasswordInputDto;
use App\Dto\User\Profile\Input\UpdateProfileInputDto;
use App\Entity\User;
use App\Mapper\ProfileMapper;
use App\Response\ApiJsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Controlador para la gestión del perfil de usuario
 */
#[Route('/api/profile', name: 'api_profile_')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ApiJsonResponse $apiJsonResponse,
        private readonly ProfileMapper $profileMapper,
    ) {}

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        $userDto = $this->profileMapper->toBasic($user);

        return $this->apiJsonResponse->successResponse($userDto);
    }

    #[Route('', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload] UpdateProfileInputDto $dto,
        #[CurrentUser] User $user,
    ): JsonResponse
    {
        $user = $this->profileMapper->fromUpdateDto($user, $dto);
        $this->entityManager->flush();

        $userDto = $this->profileMapper->toBasic($user);

        return $this->apiJsonResponse->successResponse($userDto);
    }

    #[Route('/password', name: 'update_password', methods: ['PUT'])]
    public function updatePassword(
        #[MapRequestPayload] UpdatePasswordInputDto $dto,
        #[CurrentUser] User $user
    ): JsonResponse
    {
        if (!$this->passwordHasher->isPasswordValid($user, $dto->currentPassword)) {
            return ApiJsonResponse::error(
                message: 'La contraseña actual es incorrecta',
            );
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->newPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();

        return $this->apiJsonResponse->successResponse('Contraseña actualizada con éxito');
    }

}