<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Catalog as CatalogContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Catalog implements CatalogContract
{
    /**
     * @param list<Product> $products
     * @param list<Source> $sources
     * @param list<Pack> $packs
     * @param array<string, PlanLimits|null> $limitsByPlan
     */
    public function __construct(
        private float $creditPriceInEuros,
        private array $products,
        private array $sources,
        private array $packs,
        private ?FreeTier $freeTier,
        private ?Limits $limits,
        private array $limitsByPlan,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireFloat('credit_price_eur'),
            $payload->list('products', Product::from(...)),
            $payload->list('sources', Source::from(...)),
            $payload->list('packs', Pack::from(...)),
            $payload->nested('free_tier', FreeTier::from(...)),
            $payload->nested('limits', Limits::from(...)),
            $payload->objectMap('limits_by_plan', PlanLimits::from(...)),
        );
    }

    public function creditPriceInEuros(): float
    {
        return $this->creditPriceInEuros;
    }

    /**
     * @return list<Product>
     */
    public function products(): array
    {
        return $this->products;
    }

    /**
     * @return list<Source>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    /**
     * @return list<Pack>
     */
    public function packs(): array
    {
        return $this->packs;
    }

    public function freeTier(): ?FreeTier
    {
        return $this->freeTier;
    }

    public function limits(): ?Limits
    {
        return $this->limits;
    }

    /**
     * @return array<string, PlanLimits|null>
     */
    public function limitsByPlan(): array
    {
        return $this->limitsByPlan;
    }
}
