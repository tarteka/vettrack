<?php

namespace App\Controller\API\Dashboard;

use App\Dto\Invoice\Response\InvoiceDashboardDto;
use App\Repository\InvoiceRepository;
use App\Response\ApiJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboard/invoices', name: 'api_dashboard_invoices', methods: ['GET'])]
#[IsGranted('ROLE_CLIENT')]
class GetDashboardInvoicesController extends AbstractController
{
    private InvoiceRepository $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Maneja la solicitud para obtener las facturas del dashboard
     * Devuelve una lista vacia si el usuario no tiene facturas
     * En el dashboard solo los clientes tienen facturas (no admin ni veterinarios)
     *
     * @return JsonResponse Respuesta JSON con la lista de facturas
     */
    public function __invoke(): JsonResponse
    {
        // Obtener el usuario autenticado
        $user = $this->getUser();

        // Solo los clientes tienen facturas
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        // Obtener las facturas del usuario limitadas a las últimas 2
        $invoices = $this->repository->findBy(['user' => $user ], ['invoiceDate' => 'DESC'], 2);

        $result = [];

        foreach ($invoices as $invoice) {
            if ($this->isGranted('VIEW', $invoice)) {

                $invoiceDto = new InvoiceDashboardDto($invoice);
                $result[] = $invoiceDto->toArray();
            }
        }

        return ApiJsonResponse::success($result);
    }
}