<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Concerns\BuildsQuery;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Areas as AreasContract;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\TrendMetric;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingLocatorException;
use Henzeb\Housepedia\Data\Intelligence\Locators\AreaLocator;
use Henzeb\Housepedia\Data\Intelligence\Locators\PostcodeAreaLocator;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Area;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaHierarchy;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaStatistics;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaTrends;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Demographics;

/**
 * @phpstan-consistent-constructor
 */
class Areas implements AreasContract
{
    use BuildsQuery;
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
        private ?AreaLocator $area = null,
    ) {
    }

    public function withArea(AreaLocator $area): static
    {
        return new static($this->transport, $area);
    }

    public function withNeighbourhood(string $code): static
    {
        return $this->withArea(AreaLocator::neighbourhood($code));
    }

    public function withDistrict(string $code): static
    {
        return $this->withArea(AreaLocator::district($code));
    }

    public function withMunicipality(string $code): static
    {
        return $this->withArea(AreaLocator::municipality($code));
    }

    public function withProvince(string $code): static
    {
        return $this->withArea(AreaLocator::province($code));
    }

    public function withPostcode4(string $postcode): PostcodeArea
    {
        return new PostcodeArea($this->transport, PostcodeAreaLocator::postcode4($postcode));
    }

    public function withPostcode6(string $postcode): PostcodeArea
    {
        return new PostcodeArea($this->transport, PostcodeAreaLocator::postcode6($postcode));
    }

    public function national(): static
    {
        return $this->withArea(AreaLocator::national());
    }

    public function lookupPostcode(string $postcode): AreaHierarchy
    {
        return AreaHierarchy::from(
            $this->transport->get('/v5/areas/lookup', ['postcode' => str_replace(' ', '', $postcode)])->payload(),
        );
    }

    public function lookupCoordinates(float|string $latitude, float|string $longitude): AreaHierarchy
    {
        return AreaHierarchy::from(
            $this->transport->get('/v5/areas/lookup', [
                'lat' => $this->coordinate('latitude', $latitude),
                'lon' => $this->coordinate('longitude', $longitude),
            ])->payload(),
        );
    }

    public function find(bool $includeRd = false): Area
    {
        return Area::from($this->get(__FUNCTION__, '', ['include' => $this->include($includeRd)]));
    }

    public function stats(): AreaStatistics
    {
        return AreaStatistics::from($this->get(__FUNCTION__, '/stats'));
    }

    public function trends(
        TrendMetric|string|null $metric = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        bool $includeRd = false,
    ): AreaTrends {
        return AreaTrends::from(
            $this->get(__FUNCTION__, '/trends', [
                'metric' => $this->enumValue($metric),
                'from' => $this->date($from),
                'to' => $this->date($to),
                'include' => $this->include($includeRd),
            ]),
        );
    }

    public function demographics(): Demographics
    {
        return Demographics::from($this->get(__FUNCTION__, '/demographics'));
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    private function get(string $method, string $suffix, array $query = []): Payload
    {
        $area = $this->area ?? throw new MissingLocatorException(static::class, $method);

        return $this->transport->get('/v5/areas/' . $area . $suffix, $query)->payload();
    }
}
