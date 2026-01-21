<?php

namespace App\Controller\API\Dashboard;

use App\Response\ApiJsonResponse;
use App\Services\DashboardNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboard/notifications', name: 'api_dashboard_notifications', methods: ['GET'])]
#[IsGranted('ROLE_VET')]
class GetDashboardNotificationsController extends AbstractController
{
    private readonly DashboardNotificationService $service;
    public function __construct( DashboardNotificationService $service)
    {
        $this->service = $service;
    }

    public function __invoke() : JsonResponse
    {

        $notifications = $this->service->getDashboardNotifications();
        return ApiJsonResponse::success($notifications);
    }
}