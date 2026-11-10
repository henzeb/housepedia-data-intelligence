<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaTrends as AreaTrendsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AreaTrends implements AreaTrendsContract
{
    /**
     * @param list<TrendPoint> $points
     * @param list<TrendSeries>|null $parents
     */
    public function __construct(
        private AreaReference $area,
        private string $type,
        private string $metric,
        private ?string $interval,
        private array $points,
        private ?array $parents,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('area', AreaReference::from(...)),
            $payload->requireString('type'),
            $payload->requireString('metric'),
            $payload->string('interval'),
            $payload->list('data', TrendPoint::from(...)),
            $payload->nullableList('parents', TrendSeries::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function area(): AreaReference
    {
        return $this->area;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function metric(): string
    {
        return $this->metric;
    }

    public function interval(): ?string
    {
        return $this->interval;
    }

    /**
     * @return list<TrendPoint>
     */
    public function points(): array
    {
        return $this->points;
    }

    /**
     * @return list<TrendSeries>|null
     */
    public function parents(): ?array
    {
        return $this->parents;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
