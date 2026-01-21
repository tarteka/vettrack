<?php

namespace App\Controller\API\Dashboard;

use App\Dto\Pet\PetDashboardResponseDto;
use App\Repository\PetRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboard/pets', name: 'api_dashboard_pets', methods: ['GET'])]
class GetDashboardPetsController extends AbstractController
{
    private PetRepository $petRepository;

    public function __construct(PetRepository $petRepository)
    {
        $this->petRepository = $petRepository;
    }

    /**
     * Maneja la solicitud para obtener las mascotas del dashboard
     * Devuelve una lista vacia si el usuario no tiene mascotas
     * En el dashboard solo los clientes tienen mascotas (no admin ni veterinarios)
     *
     * @return JsonResponse Respuesta JSON con la lista de mascotas
     */
    public function __invoke() : JsonResponse
    {
        // Obtener el usuario autenticado
        $user = $this->getUser();

        // Solo los clientes tienen mascotas
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $mascotas = $this->petRepository->findBy(['client' => $user, 'isActive' => true]);

        $result = [];

        foreach ($mascotas as $mascota) {

            if ($this->isGranted('VIEW', $mascota))
            {
                $mascotaDto = new PetDashboardResponseDto($mascota);
                $result[] = $mascotaDto->toArray();
            }
        }

        return ApiJsonResponse::success($result);
    }
}