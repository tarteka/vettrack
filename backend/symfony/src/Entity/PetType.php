<?php

namespace App\Entity;

use App\Repository\PetTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PetType Entity - Catálogo de tipos de mascotas (perro, gato, conejo, etc.)
 */

#[ORM\Entity(repositoryClass: PetTypeRepository::class)]
#[ORM\Table(
    name: "pet_types",
    uniqueConstraints:[
        new ORM\UniqueConstraint(name: "unique_pet_type_name", columns: ["name"])
    ]
)]
#[UniqueEntity(
    fields: ["name"],
    message: "El tipo de mascota '{{ value }}' ya existe en el catálogo."
)]
class PetType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50, unique: true)]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    // Timestamps automáticos con Gedmo
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    #[Gedmo\Timestampable(on: "create")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime")]
    #[Gedmo\Timestampable(on: "update")]
    private \DateTime $updatedAt;

    // ========== GETTERS Y SETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    // ========== MÉTODOS DE UTILIDAD ==========

    /**
     * Representación en string del tipo de mascota
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
