<?php

namespace App\Factory;

use App\Entity\Service;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Service>
 */
final class ServiceFactory extends PersistentProxyObjectFactory
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
        return Service::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'category' => ServiceCategoryFactory::random(),
            'name' => self::faker()->words(3, true),
            'description' => self::faker()->optional(0.7)->paragraph(),
            'unitPrice' => self::faker()->randomFloat(2, 20, 3000),
            'taxRate' => 21,
            'isActive' => true,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Service $service): void {})
        ;
    }
}
