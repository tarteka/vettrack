<?php

namespace App\DataFixtures;

use App\Entity\ClinicSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClinicSettingsFixtures extends Fixture
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {}

    public function load(ObjectManager $manager): void
    {
        $settings = new ClinicSettings();
        $settings->setClinicName($this->params->get('CLINIC_NAME'));
        $settings->setCif($this->params->get('CLINIC_CIF'));
        $settings->setAddress($this->params->get('CLINIC_ADDRESS'));
        $settings->setPostalCode($this->params->get('CLINIC_POSTAL_CODE'));
        $settings->setCity($this->params->get('CLINIC_CITY'));
        $settings->setProvince($this->params->get('CLINIC_PROVINCE'));
        $settings->setCountry($this->params->get('CLINIC_COUNTRY'));
        $settings->setPhone($this->params->get('CLINIC_PHONE'));
        $settings->setEmail($this->params->get('CLINIC_EMAIL'));

        $manager->persist($settings);
        $manager->flush();
    }
}