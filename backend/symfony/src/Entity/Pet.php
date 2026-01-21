<?php

namespace App\Entity;

use App\Enum\Gender;
use App\Repository\PetRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Pet Entity - Información de las mascotas de los clientes
 */

#[ORM\Entity(repositoryClass: PetRepository::class)]
#[ORM\Table(
    name: "pets",
    indexes: [
        new ORM\Index(name: "idx_user_pets" , columns: ["user_id"]),
        new ORM\Index(name: "idx_active" , columns: ["is_active"]),
        ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: "unique_microchip", columns: ["microchip"])
        ]
)]
#[UniqueEntity(
    fields: ["microchip"],
    message: "El microchip ya está en uso.",
    ignoreNull: true)]
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $client;

    #[ORM\ManyToOne(targetEntity: PetType::class)]
    #[ORM\JoinColumn(name: "pet_type_id", referencedColumnName: "id", nullable: false)]
    private PetType $petType;

    #[ORM\Column(type: "string", length: 100)]
    private string $name;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $breed = null; // Raza

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: "string", length: 10, enumType: Gender::class)]
    private Gender $gender;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $color;

    #[ORM\Column(type: "string", length: 15, unique: true, nullable: true)]
    private ?string $microchip = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $weight = null; // Peso en gramos

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $sterilized = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $insuranceProvider = null; // Aseguradora

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $insurancePolicyNumber = null; // Número de póliza

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private bool $isActive = true;

    #[ORM\Column(name: "created_at", type: "datetime")]
    #[Gedmo\Timestampable(on: "create")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime")]
    #[Gedmo\Timestampable(on: "update")]
    private \DateTimeInterface $updatedAt;

    // ========== MÉTODOS DE UTILIDAD ==========
    public function getAge(): int
    {
        if ($this->birthDate === null) {
            return 0;
        }

        $today = new DateTimeImmutable();
        return $today->diff($this->birthDate)->y;
    }

    // -------- Getters y setters ---------

    /**
     * Obtener el id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtener el propietario cliente de la mascota.
     *
     * @return User
     */
    public function getClient(): User
    {
        return $this->client;
    }

    /**
     * Establecer el propietario cliente de la mascota.
     *
     * @param User $client
     * @return self
     */
    public function setClient(User $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Obtener el tipo de mascota.
     *
     * @return PetType
     */
    public function getPetType(): PetType
    {
        return $this->petType;
    }

    /**
     * Establecer el tipo de mascota.
     *
     * @param PetType $petType
     * @return self
     */
    public function setPetType(PetType $petType): self
    {
        $this->petType = $petType;
        return $this;
    }

    /**
     * Obtener el nombre de la mascota.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Establecer el nombre de la mascota.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): void
    {
        $this->weight = $weight;
    }


    /**
     * Obtener la raza (puede ser null).
     *
     * @return string|null
     */
    public function getBreed(): ?string
    {
        return $this->breed;
    }

    /**
     * Establecer la raza.
     *
     * @param string|null $breed
     * @return self
     */
    public function setBreed(?string $breed): self
    {
        $this->breed = $breed;
        return $this;
    }

    /**
     * Obtener la fecha de nacimiento (puede ser null).
     *
     * @return \DateTimeInterface|null
     */
    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    /**
     * Establecer la fecha de nacimiento.
     *
     * @param \DateTimeInterface|null $birthDate
     * @return self
     */
    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Obtener el color (puede ser null).
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Establecer el color.
     *
     * @param string|null $color
     * @return self
     */    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Obtener el número de microchip (puede ser null).
     *
     * @return string|null
     */
    public function getMicrochip(): ?string
    {
        return $this->microchip;
    }

    /**
     * Establecer el microchip.
     *
     * @param string|null $microchip
     * @return self
     */
    public function setMicrochip(?string $microchip): self
    {
        $this->microchip = $microchip;
        return $this;
    }



    /**
     * Obtener alergias (puede ser null).
     *
     * @return string|null
     */
    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    /**
     * Establecer alergias.
     *
     * @param string|null $allergies
     * @return self
     */
    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;
        return $this;
    }

    /**
     * Obtener si está esterilizado (puede ser null).
     *
     * @return bool|null
     */
    public function getSterilized(): ?bool
    {
        return $this->sterilized;
    }

    /**
     * Establecer esterilizado.
     *
     * @param bool|null $sterilized
     * @return self
     */
    public function setSterilized(?bool $sterilized): self
    {
        $this->sterilized = $sterilized;
        return $this;
    }

    /**
     * Obtener la aseguradora (puede ser null).
     *
     * @return string|null
     */
    public function getInsuranceProvider(): ?string
    {
        return $this->insuranceProvider;
    }

    /**
     * Establecer la aseguradora.
     *
     * @param string|null $insuranceProvider
     * @return self
     */
    public function setInsuranceProvider(?string $insuranceProvider): self
    {
        $this->insuranceProvider = $insuranceProvider;
        return $this;
    }

    /**
     * Obtener el número de póliza (puede ser null).
     *
     * @return string|null
     */
    public function getInsurancePolicyNumber(): ?string
    {
        return $this->insurancePolicyNumber;
    }

    /**
     * Establecer el número de póliza.
     *
     * @param string|null $insurancePolicyNumber
     * @return self
     */
    public function setInsurancePolicyNumber(?string $insurancePolicyNumber): self
    {
        $this->insurancePolicyNumber = $insurancePolicyNumber;
        return $this;
    }

    /**
     * Obtener notas (puede ser null).
     *
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Establecer notas.
     *
     * @param string|null $notes
     * @return self
     */
    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * ¿Está activo?
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }



    /**
     * Establecer si está activo.
     *
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Obtener fecha de creación.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Obtener fecha de última actualización.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

}