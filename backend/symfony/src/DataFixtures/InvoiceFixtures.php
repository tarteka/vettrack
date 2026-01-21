<?php

namespace App\DataFixtures;

use App\Entity\InvoiceItem;
use App\Entity\User;
use App\Factory\InvoiceFactory;
use App\Factory\ServiceFactory;
use App\Repository\InvoiceRepository;
use App\Repository\ServiceRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly ServiceRepository $serviceRepository
    ){}

    public function load(ObjectManager $manager): void
    {
        InvoiceFactory::createMany(50);

        $servicesArray = $this->serviceRepository->findAll();
        $invoices = $this->invoiceRepository->findAll();

        foreach ($invoices as $invoice) {
            $randomItems = rand(1, 3);
            for ($i = 0; $i < $randomItems; $i++) {
                $item = new InvoiceItem();
                $randomIndex = array_rand($servicesArray);
                $service = $servicesArray[$randomIndex];
                $item->setService($service);
                $item->setQuantity(2);
                $invoice->addInvoiceItem($item);
            }
            $invoice->updateTotals();
            $manager->persist($invoice);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MedicalRecordFixtures::class,
            ServiceFixtures::class,
        ];
    }
}