<?php

namespace App\Entity;

use App\Repository\ClinicSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ClinicSettings Entity - Configuración única de la clínica veterinaria
 */

#[ORM\Entity(repositoryClass: ClinicSettingsRepository::class)]
#[ORM\Table(name: "clinic_settings")]
class ClinicSettings
{

    #[ORM\Id]
    #[ORM\Column(type: "integer", options: ["default" => 1])]
    private int $id = 1;  // Siempre 1, único registro

    #[ORM\Column(name: "clinic_name", type: "string", length: 200)]
    private string $clinicName;

    #[ORM\Column(name: "cif", type: "string", length: 50)]
    private string $cif;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\Column(name: "postal_code", type: "string", length: 10)]
    private string $postalCode;

    #[ORM\Column(type: "string", length: 100)]
    private string $city;

    #[ORM\Column(type: "string", length: 100)]
    private string $province;

    #[ORM\Column(type: "string", length: 100, options: ["default" => "España"])]
    private string $country = 'España';

    #[ORM\Column(type: "string", length: 20)]
    private string $phone;

    #[ORM\Column(type: "string", length: 180)]
    private string $email;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    #[Gedmo\Timestampable(on: "create")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime")]
    #[Gedmo\Timestampable(on: "update")]
    private \DateTime $updatedAt;

    // Getters y setters

    public function getId(): int
    {
        return $this->id;
    }

    public function getClinicName(): string
    {
        return $this->clinicName;
    }

    public function setClinicName(string $clinicName): self
    {
        $this->clinicName = $clinicName;
        return $this;
    }

    public function getCif(): string
    {
        return $this->cif;
    }

    public function setCif(string $cif): self
    {
        $this->cif = $cif;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

}