<?php

namespace App\Entity;

use App\Enum\MedicalRecordType;
use App\Repository\MedicalRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MedicalRecord Entity - Representa los historiales médicos de las mascotas
 */

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
#[ORM\Table(name: 'medical_records')]
#[ORM\Index(name: 'idx_pet_records', columns: ['pet_id'])]
#[ORM\Index(name: 'idx_veterinarian_records', columns: ['veterinarian_id'])]
#[ORM\Index(name: 'idx_record_date', columns: ['record_date'])]
class MedicalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(name: 'pet_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Pet $pet;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'veterinarian_id', referencedColumnName: 'id', nullable: false)]
    private User $veterinarian;

    #[ORM\Column(type: 'string', length: 25, enumType: MedicalRecordType::class)]
    private MedicalRecordType $type;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $description;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $procedures;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $recordDate;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $diagnosis = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null; // notas adicionales (solo para personal)

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    // Getters y setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;
        return $this;
    }

    public function getVeterinarian(): User
    {
        return $this->veterinarian;
    }

    public function setVeterinarian(User $veterinarian): self
    {
        $this->veterinarian = $veterinarian;
        return $this;
    }

    public function getRecordDate(): \DateTimeInterface
    {
        return $this->recordDate;
    }

    public function setRecordDate(\DateTimeInterface $recordDate): self
    {
        $this->recordDate = $recordDate;
        return $this;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(?string $diagnosis): self
    {
        $this->diagnosis = $diagnosis;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getType(): MedicalRecordType
    {
        return $this->type;
    }

    public function setType(MedicalRecordType $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getProcedures(): string
    {
        return $this->procedures;
    }

    public function setProcedures(string $procedures): void
    {
        $this->procedures = $procedures;
    }


}
