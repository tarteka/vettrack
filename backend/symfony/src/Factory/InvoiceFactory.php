<?php

namespace App\Factory;

use App\Entity\Invoice;
use App\Enum\InvoicePaymentMethod;
use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use App\Services\Invoice\InvoiceNumberGeneratorService;
use Throwable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Invoice>
 */
final class InvoiceFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceNumberGeneratorService $invoiceNumberGenerator
    ){}

    #[\Override]
    public static function class(): string
    {
        return Invoice::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     * @throws Throwable
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $medicalRecord = MedicalRecordFactory::random();
        $status = self::faker()->randomElement(InvoiceStatus::class);
        $invoiceDate = self::faker()->dateTimeBetween($medicalRecord->getCreatedAt(), 'now');
        $paymentMethod = null;
        $paymentDate = null;

        if ($status == InvoiceStatus::Paid) {
            $paymentMethod = self::faker()->randomElement(InvoicePaymentMethod::class);
            $paymentDate = self::faker()->dateTimeBetween($invoiceDate, 'now');
        }

        return [
            'invoiceNumber' => $this->invoiceNumberGenerator->generate(),
            'user' => $medicalRecord->getPet()->getClient(),
            'pet' => $medicalRecord->getPet(),
            'medicalRecord' => $medicalRecord,
            'createdBy' => UserFactory::random(), // Debe ser usuario tipo ROLE_VET o ADMIN supongo
            'invoiceDate' => $invoiceDate,
            'status' => $status,
            'paymentMethod' => $paymentMethod,
            'paymentDate' => $paymentDate,
            'notes' => self::faker()->optional()->sentence(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Invoice $invoice): void {})
        ;
    }

}
