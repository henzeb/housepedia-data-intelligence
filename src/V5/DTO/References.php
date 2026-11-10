<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\References as ReferencesContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class References implements ReferencesContract
{
    /**
     * @param list<Reference> $references
     */
    public function __construct(
        private ReferenceTarget $target,
        private array $references,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('target', ReferenceTarget::from(...)),
            $payload->list('data', Reference::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function target(): ReferenceTarget
    {
        return $this->target;
    }

    /**
     * @return list<Reference>
     */
    public function references(): array
    {
        return $this->references;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
