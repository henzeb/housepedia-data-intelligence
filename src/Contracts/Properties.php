<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Condition;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\EnergyLabel;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Environment;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Property;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\References;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Surroundings;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Transactions;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Valuation;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ValuationHistory;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ValuationPlus;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Woz;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\PropertyFieldGroup;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\ReferenceSort;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

interface Properties
{
    public function withLocator(Locator $locator): static;

    /**
     * @param list<PropertyFieldGroup|string> $fields
     */
    public function find(array $fields = [], bool $includeRd = false): Property;

    public function energyLabel(): EnergyLabel;

    public function woz(bool $indexed = false): Woz;

    public function condition(): Condition;

    public function transactions(): Transactions;

    public function hasTransactions(): bool;

    public function valuation(?DateTimeInterface $asOfDate = null): Valuation;

    public function valuationPlus(?DateTimeInterface $asOfDate = null): ValuationPlus;

    public function valuationHistory(
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        bool $includeRd = false,
    ): ValuationHistory;

    public function references(
        ?int $limit = null,
        ?int $maxDistanceInMeters = null,
        ?int $maxAgeMonths = null,
        ?bool $sameType = null,
        ?int $minLivingAreaInSquareMeters = null,
        ?int $maxLivingAreaInSquareMeters = null,
        ?int $minConstructionYear = null,
        ?int $maxConstructionYear = null,
        ?DateTimeInterface $asOfDate = null,
        ReferenceSort|string|null $sort = null,
    ): References;

    public function surroundings(): Surroundings;

    public function environment(): Environment;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;

    public function withBagId(string $bagId): static;

    public function withPostcode(string $postcode, int $houseNumber, ?string $extension = null): static;
}
