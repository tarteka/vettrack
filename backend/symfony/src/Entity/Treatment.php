<?php

namespace App\Entity;

use App\Enum\TreatmentStatus;
use App\Repository\TreatmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Treatment Entity - Representa los tratamientos médicos administrados a las mascotas
 */

#[ORM\Entity(repositoryClass: TreatmentRepository::class)]
#[ORM\Table(
    name: 'treatments',
    indexes: [
        new ORM\Index(name: 'idx_pet_treatments', columns: ['pet_id', 'status']),
        new ORM\Index(name: 'idx_status', columns: ['status']),
        new ORM\Index(name: 'idx_dates', columns: ['start_date', 'end_date']),
    ]
)]
class Treatment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MedicalRecord::class)]
    #[ORM\JoinColumn(name: 'medical_record_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private MedicalRecord $medicalRecord;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(name: 'pet_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Pet $pet;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'veterinarian_id', referencedColumnName: 'id', nullable: false)]
    private User $veterinarian;

    #[ORM\Column(type: 'string', length: 125, nullable: false)]
    private string $name; // nombre del tratamiento

    #[ORM\Column(type: 'string', length: 125, nullable: false)]
    private string $medicine; // medicamento

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $dose; // dósis

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $frequency; // frecuencia

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $instructions = null; // indicaciones de uso

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'string', length: 20, enumType: TreatmentStatus::class, options: ['default' => TreatmentStatus::Active])]
    private TreatmentStatus $status = TreatmentStatus::Active;

    #[ORM\Column(type: 'string', length: 250, nullable: true)]
    private ?string $suspendedReason = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;


    // Getters y setters...

    /**
     * Calcula la duración del tratamiento en días.
     *
     * @return string Duración en días o 'Tratamiento continuo' si no hay fecha de fin.
     */
    public function getDuration(): string
    {
        if (!$this->getEndDate()) {
            return 'Tratamiento continuo';
        } else {
            $interval = $this->getStartDate()->diff($this->getEndDate());
            return $interval->format('%a días');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedicalRecord(): MedicalRecord
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(MedicalRecord $medicalRecord): self
    {
        $this->medicalRecord = $medicalRecord;
        return $this;
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

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): TreatmentStatus
    {
        return $this->status;
    }

    public function setStatus(TreatmentStatus $status): self
    {
        $this->status = $status;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMedicine(): string
    {
        return $this->medicine;
    }

    public function setMedicine(string $medicine): void
    {
        $this->medicine = $medicine;
    }

    public function getDose(): string
    {
        return $this->dose;
    }

    public function setDose(string $dose): void
    {
        $this->dose = $dose;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): void
    {
        $this->frequency = $frequency;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getSuspendedReason(): ?string
    {
        return $this->suspendedReason;
    }

    public function setSuspendedReason(?string $suspendedReason): void
    {
        $this->suspendedReason = $suspendedReason;
    }

}
