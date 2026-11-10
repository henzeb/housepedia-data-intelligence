<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Energy as EnergyContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Energy implements EnergyContract
{
    public function __construct(
        private ?string $energyLabel,
        private ?string $energyLabelSource,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('energy_label'),
            $payload->string('energy_label_source'),
        );
    }

    public function energyLabel(): ?string
    {
        return $this->energyLabel;
    }

    public function energyLabelSource(): ?string
    {
        return $this->energyLabelSource;
    }
}
