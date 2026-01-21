<?php

namespace App\Services\Invoice;

use App\Entity\ClinicSettings;
use App\Entity\Invoice;
use App\Repository\ClinicSettingsRepository;
use Dompdf\Dompdf;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class InvoicePdfGeneratorService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Dompdf $dompdf,
        private readonly ClinicSettingsRepository $clinicSettingsRepository
    ) {}

    /**
     * Genera un PDF a partir de una factura.
     *
     * @param Invoice $invoice
     * @return string El contenido del PDF generado.
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generatePdf(Invoice $invoice): string
    {
        $clinicSettings = $this->clinicSettingsRepository->find(1);
        $html = $this->twig->render(
            'invoicePdf.html.twig',
            [
                'invoice' => $invoice,
                'clinic' => $clinicSettings
            ]
        );

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        return $this->dompdf->output();
    }
}