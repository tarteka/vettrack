<?php

namespace App\Factory;

use App\Entity\User;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(private ?UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $faker = Factory::create('es_ES');

        return [
            'email' => $faker->unique()->safeEmail(),
            'dni' => strtoupper($faker->bothify('########?')),
            'firstName' => $faker->firstName(),
            'lastName' => $faker->lastName(),
            'roles' => ['ROLE_CLIENT'],
            'phone' => $faker->numerify('+346########'),
            'address' => $faker->address(),
            'city' => $faker->city(),
            'zipCode' => $faker->postcode(),
            'country' => 'España',
            'additionalNotes' => $faker->optional(0.3)->sentence(),
            'isActive' => true,
            'isVerified' => true,
            'password' => 'password'
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function(User $user): void {
                 if ($this->passwordHasher !== null) {
                     $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

                 }
             })
        ;
    }
}
