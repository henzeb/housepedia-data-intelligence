<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use ArrayIterator;
use Iterator;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Results as ResultsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

/**
 * @template-covariant T
 * @implements ResultsContract<T>
 */
class Results implements ResultsContract
{
    /**
     * @param list<T> $data
     */
    public function __construct(
        private array $data,
        private ?Meta $meta,
    ) {
    }

    /**
     * @template TItem
     * @param callable(Payload): TItem $factory
     * @return self<TItem>
     */
    public static function of(Payload $payload, callable $factory): self
    {
        return new self(
            $payload->list('data', $factory),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    /**
     * @return list<T>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }

    /**
     * @return Iterator<int, T>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
