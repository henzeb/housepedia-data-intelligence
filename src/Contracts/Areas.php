<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Area;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaHierarchy;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaStatistics;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaTrends;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Demographics;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\TrendMetric;
use Henzeb\Housepedia\Data\Intelligence\Locators\AreaLocator;

interface Areas
{
    public function withArea(AreaLocator $area): static;

    public function withNeighbourhood(string $code): static;

    public function withDistrict(string $code): static;

    public function withMunicipality(string $code): static;

    public function withProvince(string $code): static;

    public function withPostcode4(string $postcode): PostcodeArea;

    public function withPostcode6(string $postcode): PostcodeArea;

    public function national(): static;

    public function lookupPostcode(string $postcode): AreaHierarchy;

    public function lookupCoordinates(string|float $latitude, string|float $longitude): AreaHierarchy;

    public function find(bool $includeRd = false): Area;

    public function stats(): AreaStatistics;

    public function trends(
        TrendMetric|string|null $metric = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        bool $includeRd = false,
    ): AreaTrends;

    public function demographics(): Demographics;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
