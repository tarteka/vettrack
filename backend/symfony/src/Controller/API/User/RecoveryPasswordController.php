<?php

namespace App\Controller\API\User;

use App\Entity\User;
use App\Repository\ClinicSettingsRepository;
use App\Repository\UserRepository;
use App\Response\ApiJsonResponse;
use App\Services\EmailService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

class RecoveryPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ClinicSettingsRepository $clinicSettingsRepository,
        private readonly ?RateLimiterFactory $recoveryPasswordLimiter = null,
        private readonly string $frontendUrl,
        private readonly array $demoProtectedEmails = [],
    ){}

    /**
     * Si el email es de un usuario, enviaremos las nuevas credenciales por email.
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EmailService $emailService
     * @return JsonResponse
     * @throws RandomException|TransportExceptionInterface
     */
    #[Route('/api/recovery-password', name: 'recovery_password', methods: ['POST'])]
    public function recoveryPassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EmailService $emailService
    ) :JsonResponse
    {
        // Verificar si limiter existe (solo activará en producción)
        if ($this->recoveryPasswordLimiter !== null) {
            $limiter = $this->recoveryPasswordLimiter->create($request->getClientIp() ?? 'unknown');
            $limit = $limiter->consume(1);

            if (!$limit->isAccepted())
            {
                return ApiJsonResponse::error(
                    sprintf('Demasiadas solicitudes. Inténtelo dentro %d minutos.',
                        $limit->getRetryAfter()->getTimestamp() - time()
                    ),
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            }
        }

        $email = $request->get('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user instanceof User && !in_array($user->getEmail(), array_filter($this->demoProtectedEmails), true))
        {
            $plainPassword = bin2hex(random_bytes(8));
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $this->userRepository->save($user);

            $this->sendRecoveryEmail($user, $plainPassword, $emailService);
        }
        return ApiJsonResponse::success('Si el email es de un usuario, enviaremos las nuevas credenciales por email.');
    }

    /**
     * Envía un email de recuperación de contraseña al usuario.
     *
     * @param User $user
     * @param string $plainPassword
     * @param EmailService $emailService
     * @throws TransportExceptionInterface
     */
    private function sendRecoveryEmail(User $user, string $plainPassword, EmailService $emailService): void
    {
        $clinic = $this->clinicSettingsRepository->find(1);

        $emailService->sendTemplate(
            to: $user->getEmail(),
            subject: 'Recuperación de contraseña para '. $clinic->getClinicName(),
            templatePath: 'recoveryPassword.html.twig',
            context: [
                'user' => $user,
                'password' => $plainPassword,
                'loginUrl' => $this->frontendUrl,
                'supportEmail' => $clinic->getEmail(),
            ]
        );
    }
}