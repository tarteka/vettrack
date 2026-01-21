<?php

namespace App\Factory;

use App\DataFixtures\UserFixtures;
use App\Entity\Pet;
use App\Enum\Gender;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Pet>
 */
final class PetFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Pet::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $gender = self::faker()->randomElement(Gender::class);
        $name = $gender == Gender::Male ? self::faker()->firstNameMale() : self::faker()->firstNameFemale();
        $possibleAllergies = ['Polen', 'Alimentos', 'Pulgas', 'Medicamentos'];

        return [
            'client' => UserFactory::random(), // Debería coger solo usuarios tipo cliente.
            'petType' => PetTypeFactory::random(),
            'name' => $name,
            'breed' => self::faker()->word(),
            'gender' => $gender,
            'color' => self::faker()->safeColorName(),
            'birthDate' => self::faker()->dateTimeBetween('-15 years'),
            'microchip' => self::faker()->unique()->regexify('[0-9]{15}'),
            'weight' => self::faker()->randomFloat(3, 0, 25),
            'allergies' => self::faker()->optional(0.6)->randomElement($possibleAllergies),
            'sterilized' => self::faker()->boolean(),
            'insuranceProvider' => self::faker()->company(),
            'insurancePolicyNumber' => self::faker()->regexify('[A-Z0-9]{10}'),
            'notes' => self::faker()->optional(0.4)->text(),
            'isActive' => true,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Pet $pet): void {})
        ;
    }
}
