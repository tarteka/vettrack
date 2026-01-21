<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ApiJsonResponse
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function successResponse(
        mixed $data = null,
        int $status = Response::HTTP_OK,
        bool $serialize = true
    ): JsonResponse
    {
        $responseData = [
            'success' => 'ok',
            'code' => $status,
        ];
        if ($data !== null) {
            if ($serialize) {
                $json = $this->serializer->serialize($data, 'json');
                $data = json_decode($json, true);
            }

            $responseData['data'] = $data;
        }

        return new JsonResponse($responseData, $status);
    }

    /**
     * Crea una respuesta JSON de éxito
     *
     * @param mixed $data Datos adicionales a incluir en la respuesta
     * @param int $status Código de estado HTTP (por defecto 200 OK)
     * @return JsonResponse Respuesta JSON formateada
     */
    public static function success(
        mixed $data = null,
        int $status = Response::HTTP_OK,
    ): JsonResponse
    {
        $responseData = [
            'success' => 'ok',
            'code' => $status,
        ];
        if ($data !== null) {
            $responseData['data'] = $data;
        }

        return new JsonResponse($responseData, $status);
    }

    /**
     * Crea una respuesta JSON de error
     *
     * @param string $message Mensaje de error
     * @param int $status Código de estado HTTP (por defecto 400 Bad Request)
     * @param mixed|null $details Detalles adicionales del error (opcional)
     * @return JsonResponse Respuesta JSON formateada
     */
    public static function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $details = null,
    ): JsonResponse
    {
        $responseData = [
            'error' => [
                'message' => $message,
                'code' => $status,
            ],
        ];

        if ($details !== null) {
            $responseData['error']['details'] = $details;
        }

        return new JsonResponse($responseData, $status);
    }

    public static function notFound(
        string $message,
    ): JsonResponse
    {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }
}