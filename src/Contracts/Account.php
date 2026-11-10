<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Account as AccountDetails;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\RequestLogEntry;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Usage;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\UsageGrouping;

interface Account
{
    public function lazy(): LazyAccount;

    public function details(): AccountDetails;

    /**
     * @param list<UsageGrouping|string> $groupBy
     * @return Results<Usage>
     */
    public function usage(?DateTimeInterface $from = null, ?DateTimeInterface $to = null, array $groupBy = []): Results;

    /**
     * @return Results<RequestLogEntry>
     */
    public function requests(
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        ?string $keyId = null,
        ?int $status = null,
        ?int $limit = null,
        ?string $cursor = null,
    ): Results;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
