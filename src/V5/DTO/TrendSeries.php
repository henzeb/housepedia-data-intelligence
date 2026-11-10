<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\TrendSeries as TrendSeriesContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class TrendSeries implements TrendSeriesContract
{
    /**
     * @param list<TrendPoint> $points
     */
    public function __construct(
        private string $type,
        private ?string $code,
        private array $points,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('type'),
            $payload->string('code'),
            $payload->list('data', TrendPoint::from(...)),
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    /**
     * @return list<TrendPoint>
     */
    public function points(): array
    {
        return $this->points;
    }
}
