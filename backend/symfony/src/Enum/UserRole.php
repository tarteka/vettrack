<?php
namespace App\Enum;

enum UserRole: string
{
    public const ROLE_CLIENT = 'ROLE_CLIENT';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_VET  = 'ROLE_VET';


    case Admin = self::ROLE_ADMIN;
    case Client = self::ROLE_CLIENT;
    case Vet = self::ROLE_VET;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function nameToLower(): string
    {
        return strtolower($this->name);
    }
}