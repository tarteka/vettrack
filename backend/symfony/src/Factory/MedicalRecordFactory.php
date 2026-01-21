<?php

namespace App\Factory;

use App\Entity\MedicalRecord;
use App\Enum\MedicalRecordType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<MedicalRecord>
 */
final class MedicalRecordFactory extends PersistentProxyObjectFactory
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
        return MedicalRecord::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $diagnoses = [
            'Otitis',
            'Dermatitis',
            'Gastroenteritis',
            'Infección urinaria',
            'Conjuntivitis',
            'Alergia estacional',
            'Anemia',
            'Parasitosis intestinal',
            'Artritis',
        ];

        $recordDate = self::faker()->dateTimeBetween('-2 years', 'now');

        return [
            'pet' => PetFactory::random(),
            'veterinarian' => UserFactory::random(), // Debe ser usuario tipo ROLE_VET
            'recordDate' => $recordDate,
            'diagnosis' => self::faker()->randomElement($diagnoses),
            'notes' => self::faker()->optional(0.6)->sentence(8),
            'type' => self::faker()->randomElement(MedicalRecordType::cases()),
            'description' => self::faker()->sentence(6),
            'procedures' => self::faker()->paragraph(3),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(MedicalRecord $medicalRecord): void {})
        ;
    }
}
