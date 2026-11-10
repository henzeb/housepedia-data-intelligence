<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template-covariant T
 * @extends IteratorAggregate<int, T>
 */
interface Results extends IteratorAggregate, Countable
{
    /**
     * @return list<T>
     */
    public function data(): array;

    public function meta(): ?Meta;

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable;

    public function count(): int;
}
