<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\RequestLogEntry;

interface LazyAccount
{
    /**
     * @return iterable<int, RequestLogEntry>
     */
    public function requests(
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        ?string $keyId = null,
        ?int $status = null,
        ?int $limit = null,
    ): iterable;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
