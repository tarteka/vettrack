<?php

namespace App\Controller\API;

use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HelloWorldController extends AbstractController
{
    #[Route('/api/hello-world', name: 'hello_world', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return ApiJsonResponse::success([
            'message' => 'Hello, World!'
        ]);
    }
}