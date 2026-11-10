<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Buildings as BuildingsContract;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingLocatorException;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Building;

/**
 * @phpstan-consistent-constructor
 */
class Buildings implements BuildingsContract
{
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
        private ?string $bagPandId = null,
    ) {
    }

    public function withBagPandId(string $bagPandId): static
    {
        return new static($this->transport, trim($bagPandId));
    }

    public function find(): Building
    {
        $bagPandId = $this->bagPandId ?? throw new MissingLocatorException(static::class, __FUNCTION__);

        return Building::from($this->transport->get('/v5/buildings/' . rawurlencode($bagPandId))->payload());
    }
}
