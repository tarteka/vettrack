<?php

namespace App\Dto;

use DateTimeInterface;

/**
 * Data Transfer Object para notificaciones del dashboard
 */
class DashboardNotificationDto
{
    private string $title;
    private string $message;
    private string $priority; // e.g. 'high', 'medium', 'low'.
    private int $id;
    private string $type;
    private DateTimeInterface $dateTime;


    public function __construct(
        string $title,
        string $message,
        string $priority,
        int $id,
        string $type,
        DateTimeInterface $dateTime
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->priority = $priority;
        $this->id = $id;
        $this->type = $type;
        $this->dateTime = $dateTime;

    }

    /**
     * Convierte el DTO a un array asociativo para su uso en respuestas JSON
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'priority' => $this->getPriority(),
            'id' => $this->id,
            'type' => $this->type,
            'dateTime' => $this->getDateTime()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

}