<?php

namespace App\Factory;

use App\Entity\Treatment;
use App\Enum\TreatmentStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Treatment>
 */
final class TreatmentFactory extends PersistentProxyObjectFactory
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
        return Treatment::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $startDate = self::faker()->dateTimeBetween('-2 years', 'now');
        $status = self::faker()->randomElement(TreatmentStatus::cases());

        $endDate = in_array(
            $status,
            [TreatmentStatus::Suspended, TreatmentStatus::Completed],
            true
        )
            ? self::faker()->dateTimeBetween($startDate, 'now')
            : null;

        if ($endDate !== null && $status === TreatmentStatus::Suspended) {
            $suspendedReason = self::faker()->sentence(5, true);
        } else {
            $suspendedReason = null;
        }


        $name = [
            'Tratamiento antibiótico',
            'Control antiparasitario',
            'Tratamiento para alergia',
            'Cura post-operatoria',
            'Tratamiento antiinflamatorio',
            'Cura de heridas',
            'Suplementación vitamínica',
            'Tratamiento dermatológico'
        ];

        return [
            'medicalRecord' => $record = MedicalRecordFactory::random(),
            'pet' => $record->getPet(),
            'veterinarian' => $record->getVeterinarian(),
            'name' => self::faker()->randomElement($name),
            'medicine' => self::faker()->word() . ' ' . self::faker()->randomElement(['Tabletas', 'Jarabe', 'Inyección', 'Pomada']),
            'dose' => self::faker()->randomElement(['50 mg', '100 mg', '200 mg', '5 ml', '10 ml', '15 ml', '1 comprimido', '2 comprimidos']),
            'frequency' => self::faker()->randomElement(['Una vez al día', 'Dos veces al día', 'Cada 8 horas', 'Cada 12 horas']),
            'instructions' => self::faker()->paragraph(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'suspendedReason' => $suspendedReason,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Treatment $treatment): void {})
        ;
    }
}
