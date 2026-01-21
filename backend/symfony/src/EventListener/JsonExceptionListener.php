<?php

namespace App\EventListener;

use App\Response\ApiJsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use DomainException;

/**
 * Listener para manejar excepciones y devolver respuestas JSON en rutas /api
 */
#[AsEventListener(event: 'kernel.exception', priority: 2)]
class JsonExceptionListener
{
    public function __construct(
        private KernelInterface $kernel
    ){}

    public function __invoke(ExceptionEvent $event): void
    {
        $isDev = $this->kernel->getEnvironment() === 'dev';
        $exception = $event->getThrowable();

        // procesar solo rutas /api
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Manejar AccessDeniedException (403 Forbidden)
        if ($exception instanceof AccessDeniedException) {
            $response = ApiJsonResponse::error(
                'Acceso denegado',
                Response::HTTP_FORBIDDEN,
                $isDev ? $exception->getMessage() : 'No tienes permisos para acceder a este recurso'
            );

            $event->setResponse($response);
            return;
        }

        // Manejar errores de validación (MapRequestPayload)
        if ($exception instanceof HttpException) {
            $previous = $exception->getPrevious();

            if ($previous instanceof ValidationFailedException) {
                $violations = $previous->getViolations();
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                $response = ApiJsonResponse::error('Error de validación', Response::HTTP_BAD_REQUEST, $errors);
                $event->setResponse($response);
                return;
            }
        }

        // restricción UNIQUE de la base de datos
        if ($exception instanceof UniqueConstraintViolationException) {

            $response = ApiJsonResponse::error(
                'Entrada duplicada que viola una restricción de unicidad.',
                Response::HTTP_CONFLICT,
                $isDev ?  $exception->getMessage() : null
            );

            $event->setResponse($response);
            return;
        }

        // Errores de dominio (DomainException)
        if ($exception instanceof DomainException) {
            $response = ApiJsonResponse::error(
                    $exception->getMessage(),
                    Response::HTTP_CONFLICT,
                    $isDev ? $exception->getMessage() : null
            );
            $event->setResponse($response);
            return;
        }

        // Errores HTTP (401, 403, 404, etc.)
        if ($exception instanceof HttpException) {

            $response = ApiJsonResponse::error(
                'Error HTTP',
                $exception->getStatusCode(),
                $isDev ? $exception->getMessage() : null
            );

            $event->setResponse($response);
            return;
        }

        // Otros errores (500 Internal Server Error)
        $response = ApiJsonResponse::error(
            'An unexpected error occurred',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $isDev ? $exception->getMessage() : null
        );

        $event->setResponse($response);
    }
}