<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User Entity - Representa usuarios (clientes, veterinarios, administradores)
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
#[ORM\UniqueConstraint(name: "unique_email", columns: ["email"])]
#[ORM\UniqueConstraint(name: "unique_dni", columns: ["dni"])]
#[ORM\Index(name: "idx_email", columns: ["email"])]
#[ORM\Index(name: "idx_dni", columns: ["dni"])]
#[ORM\Index(name: "idx_active", columns: ["is_active", "is_verified"])]
#[UniqueEntity(
    fields: ["email"],
    message: "El email '{{ value }}' ya está en uso."
)]
#[UniqueEntity(
    fields: ["dni"],
    message: "El DNI '{{ value }}' ya está en uso."
)]
#[UniqueEntity(
    fields: ["licenseNumber"],
    message: "El número de licencia '{{ value }}' ya está en uso.",
    ignoreNull: true
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $dni;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 50)]
    private string $firstName;

    #[ORM\Column(length: 150)]
    private string $lastName;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length:150, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $additionalNotes = null;

    // Campos específicos para veterinarios (NULL si es cliente/admin)
    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $licenseNumber = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $specialization = null;

    // Control de cuenta
    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isVerified = false;

    // Seguridad JWT
    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: "change", field: "password")]
    private ?\DateTime $passwordChangedAt = null;

    // Timestamps automáticos con Gedmo
    #[ORM\Column]
    #[Gedmo\Timestampable(on: "create")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: "update")]
    private \DateTime $updatedAt;


    // ========== MÉTODOS DE UTILIDAD ==========

    /**
     * Devuelve el nombre completo del usuario
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Representación en string del usuario
     */
    public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * Representación detallada con roles para logs
     */
    public function toStringWithRole(): string
    {
        $roles = [];
        if ($this->isAdmin()) $roles[] = UserRole::Admin->name;
        if ($this->isVeterinarian()) $roles[] = UserRole::Vet->name;
        if (empty($roles)) $roles[] = UserRole::Client->name;

        return sprintf(
            '%s [%s] <%s>',
            $this->getFullName(),
            implode(', ', $roles),
            $this->email
        );
    }

    public function getHighestRole(): string
    {
        if ($this->isAdmin()) {
            return UserRole::Admin->nameToLower();
        }

        if ($this->isVeterinarian()) {
            return UserRole::Vet->nameToLower();
        }

        return UserRole::Client->nameToLower();
    }

    /**
     * Determina si el usuario es un veterinario
     */
    public function isVeterinarian(): bool
    {
        return in_array(UserRole::Vet->value, $this->roles);
    }

    /**
     * Determina si el usuario es un administrador
     */
    public function isAdmin(): bool
    {
        return in_array(UserRole::Admin->value, $this->roles);
    }

    /**
     * Determina si el usuario es un cliente (rol básico)
     */
    public function isClient(): bool
    {
        return in_array(UserRole::Client->value, $this->roles)
            && !$this->isVeterinarian()
            && !$this->isAdmin();
    }

    // ========== GETTERS Y SETTERS ==========

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getDni(): string
    {
        return $this->dni;
    }

    public function setDni(string $dni): self
    {
        $this->dni = $dni;
        return $this;
    }

    /**
     * @return string|null
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        if (empty($this->roles)) {
            return [UserRole::Client->value];
        }

        return array_unique($this->roles);
    }

    /**
     * @param list<string> $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $roles = array_unique($roles);

        if (empty($roles)) {
            throw new \InvalidArgumentException("El array de roles no puede estar vacío");
        }

        foreach ($roles as $role) {
            $enum = UserRole::tryFrom($role);

            if (is_null($enum)) {
                throw new \InvalidArgumentException("Todos los roles deben ser valores válidos definidos en UserRole");
            }
        }

        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return $this
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    /**
     * @param string|null $licenseNumber
     * @return $this
     */
    public function setLicenseNumber(?string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSpecialization(): ?string
    {
        return $this->specialization;
    }

    /**
     * @param string|null $specialization
     * @return $this
     */
    public function setSpecialization(?string $specialization): self
    {
        $this->specialization = $specialization;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     * @return $this
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordChangedAt(): ?\DateTime
    {
        return $this->passwordChangedAt;
    }

    /**
     * @param \DateTime|null $passwordChangedAt
     * @return $this
     */
    public function setPasswordChangedAt(?\DateTime $passwordChangedAt): self
    {
        $this->passwordChangedAt = $passwordChangedAt;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @param string|null $zipCode
     */
    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getAdditionalNotes(): ?string
    {
        return $this->additionalNotes;
    }

    public function setAdditionalNotes(?string $additionalNotes): self
    {
        $this->additionalNotes = $additionalNotes;
        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
