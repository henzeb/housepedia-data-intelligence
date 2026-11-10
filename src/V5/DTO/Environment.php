<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Environment as EnvironmentContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Environment implements EnvironmentContract
{
    /**
     * @param array<string, string> $sources
     */
    public function __construct(
        private Address $address,
        private ?Foundation $foundation,
        private ?Subsidence $subsidence,
        private ?Noise $noise,
        private ?AirQuality $air,
        private ?Heat $heat,
        private ?float $lightPollution,
        private ?float $environmentalHealthRisk,
        private ?float $beeRichness,
        private array $sources,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->nested('foundation', Foundation::from(...)),
            $payload->nested('subsidence', Subsidence::from(...)),
            $payload->nested('noise', Noise::from(...)),
            $payload->nested('air', AirQuality::from(...)),
            $payload->nested('heat', Heat::from(...)),
            $payload->float('light_pollution'),
            $payload->float('environmental_health_risk'),
            $payload->float('bee_richness'),
            $payload->stringMap('sources'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function foundation(): ?Foundation
    {
        return $this->foundation;
    }

    public function subsidence(): ?Subsidence
    {
        return $this->subsidence;
    }

    public function noise(): ?Noise
    {
        return $this->noise;
    }

    public function air(): ?AirQuality
    {
        return $this->air;
    }

    public function heat(): ?Heat
    {
        return $this->heat;
    }

    public function lightPollution(): ?float
    {
        return $this->lightPollution;
    }

    public function environmentalHealthRisk(): ?float
    {
        return $this->environmentalHealthRisk;
    }

    public function beeRichness(): ?float
    {
        return $this->beeRichness;
    }

    /**
     * @return array<string, string>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
