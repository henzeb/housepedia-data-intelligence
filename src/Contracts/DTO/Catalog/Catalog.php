<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface Catalog
{
    public function creditPriceInEuros(): float;

    /**
     * @return list<Product>
     */
    public function products(): array;

    /**
     * @return list<Source>
     */
    public function sources(): array;

    /**
     * @return list<Pack>
     */
    public function packs(): array;

    public function freeTier(): ?FreeTier;

    public function limits(): ?Limits;

    /**
     * @return array<string, PlanLimits|null>
     */
    public function limitsByPlan(): array;
}
