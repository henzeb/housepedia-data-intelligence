<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\RateLimit;

interface Meta
{
    public function requestId(): ?string;

    public function asOf(): ?DateTimeImmutable;

    public function credits(): ?Credits;

    public function nextCursor(): ?string;

    /**
     * @return list<string>|null
     */
    public function withheld(): ?array;

    public function rateLimit(): ?RateLimit;
}
