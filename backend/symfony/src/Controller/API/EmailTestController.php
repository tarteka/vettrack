<?php

    namespace App\Controller\API;

    use App\Entity\User;
    use App\Response\ApiJsonResponse;
    use App\Services\EmailService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;

    class EmailTestController extends AbstractController
    {
        #[Route('/api/test-email', methods: ['POST'])]
        public function send(
            Request $request,
            EmailService $mailer
        ): JsonResponse
        {
            $email = $request->get('email');
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;

            if (!$email) {
                return ApiJsonResponse::error('Email ausente o inválido');
            }

            try {
                $mailer->sendHtml(
                    to: $email,
                    subject: 'Prueba de envío',
                    html: '<h1>Hola</h1><p>Este es un correo de prueba.</p>'
                );

               return ApiJsonResponse::success('Correo envíado correctamente a ' . $email . '.');

            } catch (TransportExceptionInterface $e) {
                return ApiJsonResponse::error($e->getMessage());
            }
        }
    }