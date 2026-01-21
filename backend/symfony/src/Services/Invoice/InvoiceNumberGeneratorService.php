<?php

namespace App\Services\Invoice;

use App\Entity\InvoiceSequence;
use App\Repository\InvoiceSequenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

readonly class InvoiceNumberGeneratorService
{
    public function __construct(
        private EntityManagerInterface    $em,
        private InvoiceSequenceRepository $invoiceSequenceRepository
    ){}

    /**
     * Genera un nuevo número de factura en el formato AAAA/NNNN.
     *
     * @return string
     * @throws Throwable
     */
    public function generate(): string
    {
        $year = (int) date('Y');
        $this->em->beginTransaction();
        try {
            $sequence = $this->invoiceSequenceRepository->getForYearWithLock($year);
            if ($sequence === null) {
                $sequence = new InvoiceSequence($year);
                $this->em->persist($sequence);
            }

            $number = $sequence->next();
            $this->em->flush();
            $this->em->commit();

            return sprintf('%s/%04d', $year, $number); // Formato: AAAA/NNNN
        }
        catch (Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

}