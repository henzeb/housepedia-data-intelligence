<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ReverseResults as ReverseResultsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

/**
 * @extends Results<ReverseAddress>
 * @implements ReverseResultsContract<ReverseAddress>
 */
class ReverseResults extends Results implements ReverseResultsContract
{
    /**
     * @param list<ReverseAddress> $data
     */
    public function __construct(
        array $data,
        ?Meta $meta,
        private ?AreaHierarchy $areas,
    ) {
        parent::__construct($data, $meta);
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->list('data', ReverseAddress::from(...)),
            $payload->nested('meta', Meta::from(...)),
            $payload->nested('areas', AreaHierarchy::from(...)),
        );
    }

    public function areas(): ?AreaHierarchy
    {
        return $this->areas;
    }
}
