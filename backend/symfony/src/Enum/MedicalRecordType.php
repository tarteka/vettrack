<?php

namespace App\Enum;

enum MedicalRecordType : string
{
    case General = 'Consulta general';
    case Surgery = 'Cirugía';
    case Emergency = 'Emergencia';
    case Review = 'Revisión';
    case Vaccination = 'Vacunación';
    case Other = 'Otro';

    public const CHOICES = [
        self::General->value,
        self::Surgery->value,
        self::Emergency->value,
        self::Review->value,
        self::Vaccination->value,
        self::Other->value,
    ];

}
