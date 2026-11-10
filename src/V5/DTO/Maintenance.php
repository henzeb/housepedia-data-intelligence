<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Maintenance as MaintenanceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Maintenance implements MaintenanceContract
{
    public function __construct(
        private ?string $interior,
        private ?string $exterior,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('interior'),
            $payload->string('exterior'),
        );
    }

    public function interior(): ?string
    {
        return $this->interior;
    }

    public function exterior(): ?string
    {
        return $this->exterior;
    }
}
