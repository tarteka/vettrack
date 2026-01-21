<?php

namespace App\Services\Invoice;

use App\Dto\Invoice\Request\CreateInvoiceRequestDto;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\MedicalRecord;
use App\Entity\Pet;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\MedicalRecordRepository;
use App\Repository\PetRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Throwable;

final readonly class InvoiceCreatorService
{
    public function __construct(
        private EntityManagerInterface        $em,
        private InvoiceNumberGeneratorService $invoiceNumberGeneratorService,
        private UserRepository                $userRepository,
        private PetRepository                 $petRepository,
        private MedicalRecordRepository       $medicalRecordRepository,
        private ServiceRepository             $serviceRepository
    ){}

    /**
     * Lógica de negocio para crear una factura.
     *
     * @param CreateInvoiceRequestDto $dto
     * @param User $createdBy
     * @return Invoice
     * @throws DomainException
     */
    public function create(CreateInvoiceRequestDto $dto, User $createdBy): Invoice
    {
        return $this->em->wrapInTransaction(function () use ($dto, $createdBy): Invoice {

            $client = $this->userRepository->find($dto->userId);
            if (!$client instanceof User) {
                throw new DomainException('Cliente no encontrado');
            }

            $pet = $this->petRepository->find($dto->petId);
            if (!$pet instanceof Pet) {
                throw new DomainException('Mascota no encontrada');
            }

            if ($pet->getClient()->getId() !== $client->getId()) {
                throw new DomainException('La mascota no pertenece al cliente indicado');
            }

            $invoice = new Invoice();
            $invoice->setInvoiceNumber(
                $this->invoiceNumberGeneratorService->generate()
            );
            $invoice->setCreatedBy($createdBy);
            $invoice->setUser($client);
            $invoice->setPet($pet);
            $invoice->setInvoiceDate(new \DateTimeImmutable($dto->invoiceDate));
            $invoice->setNotes($dto->notes);

            $medicalRecord = $this->medicalRecordRepository
                ->findLatestByPetId($pet->getId());

            if ($medicalRecord instanceof MedicalRecord) {
                $invoice->setMedicalRecord($medicalRecord);
            }

            foreach ($dto->invoiceItems as $itemDto) {
                $service = $this->serviceRepository->find($itemDto->serviceId);

                if (!$service instanceof Service) {
                    throw new DomainException('Servicio no encontrado');
                }

                if (!$service->isActive()) {
                    throw new DomainException('El servicio no está activo');
                }

                $item = new InvoiceItem();
                $item->setService($service);
                $item->setQuantity($itemDto->quantity); // recalcula precios internamente

                $invoice->addInvoiceItem($item);
            }

            if ($invoice->getInvoiceItems()->isEmpty()) {
                throw new DomainException(
                    'La factura debe tener al menos una línea de servicio.'
                );
            }

            $invoice->updateTotals();

            $this->em->persist($invoice);

            return $invoice;
        });
    }
}