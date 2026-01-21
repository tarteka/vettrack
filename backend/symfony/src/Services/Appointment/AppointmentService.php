<?php

namespace App\Services\Appointment;

use App\Dto\Appointment\Request\CreateAppointmentAdminRequestDto;
use App\Dto\Appointment\Request\UpdateAppointmentAdminRequestDto;
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
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class AppointmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AppointmentRepository  $appointmentRepository,
        private PetRepository $petRepository,
        private AppointmentTypeRepository $appointmentTypeRepository,
        private AppointmentSlotRepository $appointmentSlotRepository,
        private UserRepository $userRepository,
    ){}

    public function confirmAppointment(int $id, User $vet): Appointment
    {
        return $this->entityManager->wrapInTransaction(function () use ($id, $vet) {
           $appointment = $this->appointmentRepository->find($id);

           if (!$appointment instanceof Appointment) {
               throw new DomainException('La cita no existe.');
           }
           if ($appointment->getStatus() !== AppointmentStatus::Requested) {
               throw new DomainException('La cita no se puede confirmar.');
           }

           $appointment->confirm($vet);
           return $appointment;
        });
    }

    /**
     * Cancela una cita. Solo se pueden cancelar citas en estado Requested o Confirmed.
     *
     * @param int $id
     * @return Appointment
     * @throws DomainException
     */
    public function cancelAppointment(int $id): Appointment
    {
        return $this->entityManager->wrapInTransaction(function () use ($id) {
           $appointment = $this->appointmentRepository->find($id);

           if (!$appointment instanceof Appointment) {
               throw new DomainException('La cita no existe.');
           }
           if (!$appointment->isActive()) {
               throw new DomainException('La cita no se puede cancelar.');
           }

           // Liberar el slot
           $appointment->getAppointmentSlot()->release();
           $appointment->cancelByClinic();
           return $appointment;
        });
    }

    public function create(CreateAppointmentAdminRequestDto $dto, User $createdBy) : Appointment
    {
        return $this->entityManager->wrapInTransaction(function () use ($dto, $createdBy) {

            $pet = $this->petRepository->find($dto->petId);

            if (!$pet instanceof Pet) {
                throw new DomainException('La mascota no existe.');
            }

            $vet = $this->userRepository->find($dto->vetId);

            if (!$vet instanceof User) {
                throw new DomainException('El veterinario no existe.');
            }

            if (!$vet->isVeterinarian()) {
                throw new DomainException('El usuario no es un veterinario.');
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
            $appointment->setVeterinarian($vet);
            $appointment->setCreatedBy($createdBy);
            $appointment->setAppointmentType($appointmentType);
            $appointment->setAppointmentSlot($appointmentSlot);
            $appointment->setReason($dto->reason);
            $appointment->setStatus(AppointmentStatus::Confirmed);

            $this->entityManager->persist($appointment);

            return $appointment;
        });
    }

    /**
     * Actualiza los datos de una cita. Solo se pueden actualizar citas.
     * Las citas canceladas no pueden ser modificadas.
     *
     * @param int $appointmentId
     * @param UpdateAppointmentAdminRequestDto $dto
     * @return Appointment
     */
    public function update(
        int $appointmentId,
        UpdateAppointmentAdminRequestDto $dto
    ): Appointment
    {
        return $this->entityManager->wrapInTransaction(function () use ($appointmentId, $dto)
        {
            $appointment = $this->appointmentRepository->findForUpdate($appointmentId);
            if (!$appointment instanceof Appointment)
            {
                throw new DomainException('La cita no existe.');
            }

            if ($appointment->getStatus() === AppointmentStatus::Cancelled) {
                throw new DomainException('No se puede modificar una cita cancelada.');
            }

            $newAppointmentType = $this->appointmentTypeRepository->find($dto->appointmentTypeId);
            if (!$newAppointmentType instanceof AppointmentType)
            {
                throw new DomainException('El tipo de consulta no existe.');
            }

            $newVeterinarian = $this->userRepository->find($dto->vetId);
            if (!$newVeterinarian instanceof User)
            {
                throw new DomainException('El veterinario no existe.');
            }

            $currentSlot = $appointment->getAppointmentSlot();
            if ($currentSlot->getId() !== $dto->appointmentSlotId)
            {
                // Bloquear nuevo slot para evitar condición de carrera
                $newSlot = $this->appointmentSlotRepository->findForUpdate($dto->appointmentSlotId);

                if (!$newSlot instanceof AppointmentSlot )
                {
                    throw new DomainException('El nuevo turno de cita no existe.');
                }

                // Liberar el slot actual y reservar el nuevo
                $currentSlot->release();
                $newSlot->reserve();
                $appointment->setAppointmentSlot($newSlot);
            }

            $appointment->setVeterinarian($newVeterinarian);
            $appointment->setAppointmentType($newAppointmentType);
            $appointment->setReason($dto->reason);

            return $appointment;

        });
    }
}