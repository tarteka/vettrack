<?php

namespace App\Services\Invoice;

use App\Entity\Invoice;
use App\Repository\ClinicSettingsRepository;
use App\Repository\InvoiceRepository;
use App\Services\EmailService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class InvoiceEmailSenderService
{
    public function __construct(
        private EmailService               $emailService,
        private InvoicePdfGeneratorService $invoicePdfGenerator,
        private ClinicSettingsRepository   $clinicSettingsRepository,
    ){}

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function send(Invoice $invoice): void
    {
        $user = $invoice->getUser();

        $clinic = $this->clinicSettingsRepository->find(1);

        if (!$user->getEmail())
        {
            throw new \DomainException('El cliente no tiene un email asociado');
        }

        //Generar PDF
        $pdf = $this->invoicePdfGenerator->generatePdf($invoice);

        //Enviar email
        $this->emailService->sendTemplateWithPdf(
            to: $user->getEmail(),
            subject: 'Su factura ' . $invoice->getInvoiceNumber(),
            templatePath: 'invoicePdfByEmail.html.twig',
            context: [
                'invoice' => $invoice,
                'clinic' => $clinic
            ],
            pdfContent: $pdf,
            filename: 'factura_' . $invoice->getInvoiceNumber() . '.pdf'
        );
    }
}