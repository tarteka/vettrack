<?php

namespace App\Factory;

use App\Entity\Appointment;
use App\Entity\AppointmentSlot;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentSlotRepository;
use App\Repository\UserRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Appointment>
 */
final class AppointmentFactory extends PersistentProxyObjectFactory
{
    private static array $availableSlots = [];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private readonly AppointmentSlotRepository $appointmentSlotRepository,
        private readonly UserRepository            $userRepository
    )
    {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return Appointment::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        // Cargamos los slots disponibles y los aleatorizamos
        if (empty(self::$availableSlots)) {
            self::$availableSlots = $this->appointmentSlotRepository
                ->findBy(['isAvailable' => true]);
            shuffle(self::$availableSlots);
        }

        if (empty(self::$availableSlots)) {
            throw new \RuntimeException('No hay más slots disponibles');
        }

        /** @var AppointmentSlot $slot */
        $slot = array_shift(self::$availableSlots);

        $vet = $this->userRepository->findRandomVet();
        if (!$vet) {
            throw new \RuntimeException('No hay veterinarios en la base de datos');
        }

        $creator = $this->userRepository->findRandomAdminOrVet();
        if (!$creator) {
            throw new \RuntimeException('No hay admin ni veterinarios para crear citas');
        }

        return [
            'pet' => PetFactory::random(),
            'appointmentSlot' => $slot,
            'appointmentType' => AppointmentTypeFactory::random(),
            'veterinarian' => $vet,
            'createdBy' => $creator,
            'reason' => self::faker()->sentence(),
            'status' => AppointmentStatus::Confirmed,
            'notes' => self::faker()->optional()->sentence(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function(Appointment $appointment): void {
            // El slot quedará ocupado
            $appointment->getAppointmentSlot()->setIsAvailable(false);
        });
    }
}
