<?php

namespace App\Controller\API\Invoice;

use App\Dto\Invoice\Request\CreateInvoiceRequestDto;
use App\Dto\Invoice\Request\PayInvoiceRequestDto;
use App\Dto\Invoice\Request\UpdateInvoiceRequestDto;
use App\Entity\Invoice;
use App\Entity\User;
use App\Mapper\InvoiceMapper;
use App\Repository\InvoiceRepository;
use App\Response\ApiJsonResponse;
use App\Security\Voter\InvoiceVoter;
use App\Services\EmailService;
use App\Services\Invoice\InvoiceCreatorService;
use App\Services\Invoice\InvoiceEmailSenderService;
use App\Services\Invoice\InvoicePdfGeneratorService;
use App\Services\Invoice\InvoiceUpdaterService;
use DateTimeImmutable;
use DomainException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route('/api/invoices', name: 'invoices_')]
class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceMapper $invoiceMapper,
        private readonly InvoiceCreatorService $invoiceCreatorService,
        private readonly InvoiceUpdaterService $invoiceUpdaterService,
        private readonly InvoicePdfGeneratorService $invoicePdfGenerator,
        private readonly InvoiceEmailSenderService $invoiceEmailSenderService,
    ){}

    /**
     * Devuelve todas las facturas.
     * Admin y Veterinarios pueden ver todas las facturas.
     * Clientes solo pueden ver sus propias facturas.
     * @return JsonResponse
     */
    #[Route('',name:'find_all', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if (($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            $invoices = $this->invoiceRepository->findAllOrderedByDateDesc();
        }

        else {
            $invoices = $this->invoiceRepository->findByUserOrderedByDateDesc($user);
        }

        return ApiJsonResponse::success($this->invoiceMapper->toResponseDtoCollection($invoices));
    }

    /**
     * Devuelve una factura por su ID.
     * Admin y Veterinarios pueden ver todas las facturas.
     * Clientes solo pueden ver sus propias facturas.
     * @param int $invoiceId
     * @return JsonResponse
     */
    #[Route('/{invoiceId}',name:'find_one', methods: ['GET'])]
    public function findById(int $invoiceId): JsonResponse
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error('Factura no encontrada', Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(InvoiceVoter::VIEW, $invoice);


        return ApiJsonResponse::success(
            $this->invoiceMapper->toResponseDto($invoice)
        );
    }

    /**
     * Crea una nueva factura.
     * Solo admin y veterinarios pueden crear una factura.
     *
     * @param CreateInvoiceRequestDto $dto
     * @return JsonResponse
     */
    #[Route('',name:'create', methods: ['POST'])]
    public function newInvoice(
        #[MapRequestPayload] CreateInvoiceRequestDto $dto
    ) : JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return ApiJsonResponse::error('No autenticado', Response::HTTP_UNAUTHORIZED);
        }

        if (!($this->isGranted('ROLE_VET') || $this->isGranted('ROLE_ADMIN')))
        {
            return ApiJsonResponse::error('No tienes permisos para acceder este recurso', Response::HTTP_FORBIDDEN);
        }

        $invoice = $this->invoiceCreatorService->create($dto, $user);
        return ApiJsonResponse::success(
            $this->invoiceMapper->toResponseDto($invoice),
            Response::HTTP_CREATED
        );
    }

    /**
     * Actualiza una factura existente.
     * No se puede modificar una factura pagada o cancelada.
     * Solo se permite modificar servicios y notas.
     * Solo admin y veterinarios pueden modificar una factura.
     *
     * @param int $invoiceId
     * @param UpdateInvoiceRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{invoiceId}',name:'update', methods: ['PUT','PATCH'])]
    public function updateInvoice(
        int $invoiceId,
        #[MapRequestPayload] UpdateInvoiceRequestDto $dto
    ): JsonResponse
    {
        // Solo ADMIN y VET pueden modificar facturas
        if (!($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VET'))) {
            return ApiJsonResponse::error(
                'No tienes permisos para modificar esta factura',
                Response::HTTP_FORBIDDEN
            );
        }

        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error('Factura no encontrada', Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->invoiceUpdaterService->update($invoice, $dto);

        return ApiJsonResponse::success(
            $this->invoiceMapper->toResponseDto($invoice)
        );
    }

    /**
     * Elimina una factura.
     * Solo admin puede eliminar una factura.
     * @param int $invoiceId
     * @return JsonResponse
     */
    #[Route('/{invoiceId}',name:'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteInvoice(int $invoiceId): JsonResponse
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error('Factura no encontrada', Response::HTTP_NOT_FOUND);
        }

        if (!$invoice->canBeDeleted()) {
            return ApiJsonResponse::error(
                'No se puede eliminar una factura que no esté en estado Pendiente o Retrasada'
            );
        }

        $this->invoiceRepository->remove($invoice);

        return ApiJsonResponse::success('Factura eliminada correctamente');
    }

    /**
     * Marca una factura como pagada.
     * Guarda también la forma y la fecha de pago.
     * Solo admin y veterinarios pueden marcar una factura como pagada.
     * @param int $invoiceId
     * @param PayInvoiceRequestDto $dto
     * @return JsonResponse
     */
    #[Route('/{invoiceId}/pay',name:'pay', methods: ['POST'])]
    public function setAsPaid(
        int $invoiceId,
        #[MapRequestPayload] PayInvoiceRequestDto $dto
    ): JsonResponse
    {
        if (!($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VET'))) {
            return ApiJsonResponse::error(
                'No tienes permisos para modificar esta factura',
                Response::HTTP_FORBIDDEN
            );
        }

        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error('Factura no encontrada', Response::HTTP_NOT_FOUND);
        }

        $paymentDate = null;

        if ($dto->paymentDate !== null) {

            $value = trim($dto->paymentDate);

            if ($value !== '') {
                $paymentDate = DateTimeImmutable::createFromFormat('Y-m-d', $value);

                if ($paymentDate === false) {
                    throw new DomainException('Formato de fecha inválido (YYYY-MM-DD)');
                }
            }
        }

        $invoice->markAsPaid($dto->paymentMethod, $paymentDate);
        $this->invoiceRepository->save($invoice);

        return ApiJsonResponse::success('Factura pagada correctamente');
    }

    /**
     * Marca una factura como cancelada.
     * Solo admin y veterinarios pueden marcar una factura como cancelada.
     * @param int $invoiceId
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/{invoiceId}/cancel',name:'cancel', methods: ['POST'])]
    public function cancel(int $invoiceId): JsonResponse
    {
        if (!($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VET'))) {
            return ApiJsonResponse::error(
                'No tienes permisos para modificar esta factura',
                Response::HTTP_FORBIDDEN
            );
        }

        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error('Factura no encontrada', Response::HTTP_NOT_FOUND);
        }

        $invoice->markAsCancelled();
        $this->invoiceRepository->save($invoice);

        return ApiJsonResponse::success('Factura cancelada correctamente');

    }

    /**
     * Endpoint que devuelve un PDF de una factura.
     *
     * @param int $invoiceId
     * @return Response
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/{invoiceId}/pdf',name:'download_pdf', methods: ['GET'])]
    public function downloadPdf(int $invoiceId): Response
    {
        $invoice = $this->invoiceRepository->find($invoiceId);
        if (!$invoice instanceof Invoice) {
            return ApiJsonResponse::error(
                'Factura no encontrada',
                Response::HTTP_NOT_FOUND
            );
        }

        $this->denyAccessUnlessGranted(InvoiceVoter::VIEW, $invoice);

        $pdf = $this->invoicePdfGenerator->generatePdf($invoice);

        return new Response(
            $pdf,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="factura_'.$invoice->getInvoiceNumber().'.pdf"'
            ]
        );
    }

    /**
     * Envía una factura por email al cliente.
     * Solo admin y veterinarios pueden enviar una factura por email.
     * @param int $invoiceId
     * @return JsonResponse
     */
    #[Route('/{invoiceId}/send-email', name: 'send_email', methods: ['POST'])]
    #[IsGranted('ROLE_VET')]
    public function sendInvoiceByEmail(int $invoiceId): JsonResponse
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice instanceof Invoice) {
            throw new DomainException('Factura no encontrada');
        }

        try {
            $this->invoiceEmailSenderService->send($invoice);
        } catch (TransportExceptionInterface $e) {
            return ApiJsonResponse::error(
                'No se pudo enviar el email. Inténtalo más tarde.',
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return ApiJsonResponse::error(
                'Error interno al generar el email de la factura.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiJsonResponse::success('Factura enviada por email correctamente');

    }
}