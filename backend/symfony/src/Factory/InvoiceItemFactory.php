<?php

namespace App\Factory;

use App\Entity\InvoiceItem;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<InvoiceItem>
 */
final class InvoiceItemFactory extends PersistentProxyObjectFactory
{
    private int $quantity = 1;

    public function withQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

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
        return InvoiceItem::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'service' => ServiceFactory::random(),
            'quantity' => $this->quantity
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function(InvoiceItem $invoiceItem): void {
            $invoiceItem->updatePrices();
        })
        ;
    }
}
