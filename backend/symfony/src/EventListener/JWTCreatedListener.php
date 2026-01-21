<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class JWTCreatedListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['id']        = $user->getId();
        $data['firstName'] = $user->getFirstName();
        $data['lastName']  = $user->getLastName();
        $data['fullName']  = $user->getFullName();

        $event->setData($data);
    }
}