<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaHierarchy as AreaHierarchyContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AreaHierarchy implements AreaHierarchyContract
{
    public function __construct(
        private ?AreaReference $neighbourhood,
        private ?AreaReference $district,
        private ?AreaReference $municipality,
        private ?AreaReference $province,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->nested('neighbourhood', AreaReference::from(...)),
            $payload->nested('district', AreaReference::from(...)),
            $payload->nested('municipality', AreaReference::from(...)),
            $payload->nested('province', AreaReference::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function neighbourhood(): ?AreaReference
    {
        return $this->neighbourhood;
    }

    public function district(): ?AreaReference
    {
        return $this->district;
    }

    public function municipality(): ?AreaReference
    {
        return $this->municipality;
    }

    public function province(): ?AreaReference
    {
        return $this->province;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
