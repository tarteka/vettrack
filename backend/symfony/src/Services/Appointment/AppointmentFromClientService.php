<?php

namespace App\Services\Appointment;

use App\Dto\Appointment\Request\CreateAppointmentClientRequestDto;
use App\Dto\Appointment\Request\UpdateAppointmentClientRequestDto;
use App\Entity\Appointment;
use App\Entity\AppointmentSlot;
use App\Entity\AppointmentType;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use App\Repository\AppointmentSlotRepository;
use App\Repository\AppointmentTypeRepository;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class AppointmentFromClientService
{
    public function __construct(
        private EntityManagerInterface    $entityManager,
        private AppointmentSlotRepository $appointmentSlotRepository,
        private AppointmentTypeRepository $appointmentTypeRepository,
        private AppointmentRepository     $appointmentRepository,
        private PetRepository             $petRepository,
    ){}

    /**
     * Crea una nueva cita para una mascota de un cliente.
     * Solo clientes autenticados pueden acceder a este recurso.
     * Se crea con el estado de REQUESTED (solicitada).
     * @param CreateAppointmentClientRequestDto $dto
     * @param User $client
     * @return Appointment
     * @throws DomainException
     */
    public function create(
        CreateAppointmentClientRequestDto $dto,
        User $client
    ) : Appointment
    {
        return $this->entityManager->wrapInTransaction(function() use ($dto, $client) {

            $pet = $this->petRepository->find($dto->petId);

            if (!$pet instanceof Pet) {
                throw new DomainException('La mascota no existe.');
            }

            if ($pet->getClient()->getId() !== $client->getId()) {
                throw new DomainException('No tienes permiso para esta mascota.');
            }

            $appointmentType = $this->appointmentTypeRepository
                ->find($dto->appointmentTypeId);

            if (!$appointmentType instanceof AppointmentType) {
                throw new DomainException('El tipo de consulta no existe.');
            }

            $appointmentSlot = $this->appointmentSlotRepository
                ->findForUpdate($dto->appointmentSlotId);

            if (!$appointmentSlot instanceof AppointmentSlot) {
                throw new DomainException('El turno de cita no existe.');
            }

            // Verificamos que el slot esté disponible y lo reservamos
            $appointmentSlot->reserve();

            $appointment = new Appointment();
            $appointment->setPet($pet);
            $appointment->setAppointmentSlot($appointmentSlot);
            $appointment->setAppointmentType($appointmentType);
            $appointment->setReason($dto->reason);
            $appointment->setStatus(AppointmentStatus::Requested);
            $appointment->setCreatedBy($client);

            $this->entityManager->persist($appointment);

            return $appointment;
        });
    }

    /**
     * Cancela una cita de la que el cliente es propietario.
     * Solo clientes autenticados pueden acceder a este recurso.
     * @param int $appointmentId
     * @param User $client
     * @return Appointment
     * @throws DomainException
     */
    public function cancel(int $appointmentId, User $client) : Appointment
    {
        return $this->entityManager->wrapInTransaction(function() use ($appointmentId, $client) {

            $appointment = $this->appointmentRepository->findForUpdate($appointmentId);

            if (!$appointment instanceof Appointment) {
                throw new DomainException('La cita no existe.');
            }

            if ($appointment->getPet()->getClient()->getId() !== $client->getId()) {
                throw new DomainException('No tienes permiso para cancelar esta cita.');
            }

            if ($appointment->getStatus() !== AppointmentStatus::Requested) {
                throw new DomainException(
                    'Solo puedes cancelar citas pendientes de confirmación.'
                );
            }

            // Liberar el slot y cancelar la cita
            $appointment->getAppointmentSlot()->release();
            $appointment->cancelByClient();

            return $appointment;
        });
    }

    /**
     * Actualiza una cita de la que el cliente es propietario.
     * Solo clientes autenticados pueden acceder a este recurso.
     * @param int $appointmentId
     * @param UpdateAppointmentClientRequestDto $dto
     * @param User $client
     * @return Appointment
     * @throws DomainException
     */
    public function update(
        int $appointmentId,
        UpdateAppointmentClientRequestDto $dto,
        User $client
    ): Appointment
    {
        return $this->entityManager->wrapInTransaction(function() use ($appointmentId, $dto, $client) {

            // Bloquear cita para evitar condición de carrera
            $appointment = $this->appointmentRepository->findForUpdate($appointmentId);

            if (!$appointment instanceof Appointment) {
                throw new DomainException('La cita no existe.');
            }

            if ($appointment->getPet()->getClient()->getId() !== $client->getId()) {
                throw new DomainException('No tienes permiso para modificar esta cita.');
            }

            if ($appointment->getStatus() !== AppointmentStatus::Requested) {
                throw new DomainException(
                    'Solo puedes modificar citas pendientes de confirmación.'
                );
            }

            $pet = $this->petRepository->find($dto->petId);

            if (!$pet instanceof Pet) {
                throw new DomainException('La mascota no existe.');
            }

            if ($pet->getClient()->getId() !== $client->getId()) {
                throw new DomainException('No tienes permiso para esta mascota.');
            }

            $appointmentType = $this->appointmentTypeRepository->find($dto->appointmentTypeId);
            if (!$appointmentType instanceof AppointmentType) {
                throw new DomainException('El tipo de consulta no existe.');
            }

            $currentSlot = $appointment->getAppointmentSlot();

            if ($currentSlot->getId() !== $dto->appointmentSlotId) {

                // Bloquear nuevo slot para evitar condición de carrera
                $newSlot = $this->appointmentSlotRepository->findForUpdate($dto->appointmentSlotId);

                if (!$newSlot instanceof AppointmentSlot ) {
                    throw new DomainException('El nuevo turno de cita no existe.');
                }

                // Liberar el slot actual y reservar el nuevo
                $currentSlot->release();
                $newSlot->reserve();
                $appointment->setAppointmentSlot($newSlot);
            }

            $appointment->setPet($pet);
            $appointment->setAppointmentType($appointmentType);
            $appointment->setReason($dto->reason);

            return $appointment;

        });
    }
}