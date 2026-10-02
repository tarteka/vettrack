<?php

namespace App\Controller\API\User\Admin;

use App\Dto\Pet\PetSummaryDto;
use App\Dto\User\Admin\Input\CreateUserInputDto;
use App\Dto\User\Admin\Input\UpdateUserInputDto;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\UserRole;
use App\Mapper\UserAdminMapper;
use App\Repository\AppointmentRepository;
use App\Repository\ClinicSettingsRepository;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Response\ApiJsonResponse;
use App\Security\Voter\UserVoter;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users', name: 'api_users_')]
#[IsGranted(UserRole::ROLE_VET)]
class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ApiJsonResponse $apiJsonResponse,
        private readonly UserAdminMapper $userAdminMapper,
        private readonly ClinicSettingsRepository $clinicSettingsRepository,
        private readonly string $frontendUrl,
        private readonly array $demoProtectedEmails = [],
    )
    {
    }

    /**
     * Crea un nuevo usuario, enviando un email de bienvenida.
     * - Solo administradores pueden crear usuarios.
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createUser(
        #[MapRequestPayload] CreateUserInputDto $dto,
        UserPasswordHasherInterface $passwordHasher,
        EmailService $emailService
    ): JsonResponse
    {
        if ($this->userRepository->findOneBy(['email' => $dto->email])) {
            return ApiJsonResponse::error('El email ya está en uso');
        }

        $user = $this->userAdminMapper->fromCreateDto($dto);

        $plainPassword = bin2hex(random_bytes(8));
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        $this->sendWelcomeEmail($user, $plainPassword, $emailService);

        $userDto = $this->userAdminMapper->toBasic($user);
        $data = [
            'user' => $userDto,
            'tempPassword' => $plainPassword
        ];

       return $this->apiJsonResponse->successResponse($data);
    }

    #[Route('', name: 'get_all_users', methods: ['GET'])]
    #[IsGranted('ROLE_VET')]
    public function getAllUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $userDtos = array_map(function (User $user) {
            return $this->userAdminMapper->toAccountList($user);
        }, $users);

        return $this->apiJsonResponse->successResponse($userDtos);
    }

    #[Route('/clients', name: 'get_all_clients', methods: ['GET'])]
    #[IsGranted(UserRole::ROLE_VET)]
    public function getAllClients(): JsonResponse
    {
        $rows = $this->userRepository->findActiveClientsWithPetCount();

        $userDtos = array_map(function (array $row) {
            return $this->userAdminMapper->toClientList($row['user'], $row['petCount']);
        }, $rows);

        return $this->apiJsonResponse->successResponse($userDtos);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    #[IsGranted(attribute: UserVoter::VIEW, subject: 'user')]
    public function getById(
        #[MapEntity(id: 'id')] ?User $user,
        AppointmentRepository $appointmentRepository,
        PetRepository $petRepository,
    ): JsonResponse
    {
        if (!$user) {
            return ApiJsonResponse::notFound('User not found');
        }

        $pets = $petRepository->findBy(['client' => $user]);

        $petDtos = array_map(function (Pet $pet) use ($appointmentRepository) {
            $lastAppointment = $appointmentRepository->findLastCompletedByPet($pet);
            return new PetSummaryDto($pet, $lastAppointment);
        }, $pets);

        $userDetailDto = $this->userAdminMapper->toDetail($user, $petDtos);

        return $this->apiJsonResponse->successResponse($userDetailDto);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    #[IsGranted(attribute: UserVoter::EDIT, subject: 'user')]
    public function updateUserById(
        #[MapEntity(id: 'id')] ?User $user,
        #[MapRequestPayload] UpdateUserInputDto $dto,
    ): JsonResponse
    {
        if (!$user) {
            return ApiJsonResponse::notFound('User not found');
        }

        if (in_array($user->getEmail(), array_filter($this->demoProtectedEmails), true)) {
            $dto->email = $user->getEmail();
        }

        $user = $this->userAdminMapper->fromUpdateDto($user, $dto);

        if ($this->isGranted(UserVoter::EDIT_ROLES, $user)) {
            if ($dto->roles !== null) $user->setRoles($dto->roles);
        }

        $this->entityManager->flush();

        $userDto = $this->userAdminMapper->toBasic($user);
        return $this->apiJsonResponse->successResponse($userDto);
    }

    #[Route('/{id}', name: 'delete_by_id', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteById(#[MapEntity(id: 'id')] ?User $user, PetRepository $petRepository): JsonResponse
    {
        if (!$user) {
            return ApiJsonResponse::notFound('User not found');
        }

        if (!$user->isActive()) {
            return $this->apiJsonResponse->successResponse();
        }

        $user->setIsActive(false);
        $petRepository->setAllStatusByUser($user, false);
        $this->entityManager->flush();

        return $this->apiJsonResponse->successResponse();
    }

    #[Route('/{id}/restore', name: 'restore_by_id', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function restoreById(#[MapEntity(id: 'id')] ?User $user, PetRepository $petRepository): JsonResponse
    {
        if (!$user) {
            return ApiJsonResponse::notFound('User not found');
        }

        if ($user->isActive()) {
            return $this->apiJsonResponse->successResponse();
        }

        $user->setIsActive(true);
        $petRepository->setAllStatusByUser($user, true);
        $this->entityManager->flush();

        return $this->apiJsonResponse->successResponse();
    }

    /**
     * Envía un email de bienvenida al nuevo usuario con la contraseña temporal.
     *
     * @param User $user
     * @param string $plainPassword
     * @param EmailService $emailService
     * @return void
     * @throws TransportExceptionInterface
     */
    private function sendWelcomeEmail(User $user, string $plainPassword, EmailService $emailService): void
    {
        $clinic = $this->clinicSettingsRepository->find(1);

        $emailService->sendTemplate(
            to: $user->getEmail(),
            subject: 'Bienvenido a '. $clinic->getClinicName(),
            templatePath: 'welcome.html.twig',
            context: [
                'user' => $user,
                'password' => $plainPassword,
                'loginUrl' => $this->frontendUrl,
                'supportEmail' => $clinic->getEmail(),
            ]
        );
    }
}