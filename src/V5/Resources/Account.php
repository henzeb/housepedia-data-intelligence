<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Concerns\BuildsQuery;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Account as AccountContract;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\UsageGrouping;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Account as AccountDetails;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\RequestLogEntry;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Usage;

class Account implements AccountContract
{
    use BuildsQuery;
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
    ) {
    }

    public function lazy(): LazyAccount
    {
        return new LazyAccount($this->transport);
    }

    public function details(): AccountDetails
    {
        return AccountDetails::from($this->transport->get('/v5/account')->payload());
    }

    /**
     * @param list<UsageGrouping|string> $groupBy
     * @return Results<Usage>
     */
    public function usage(?DateTimeInterface $from = null, ?DateTimeInterface $to = null, array $groupBy = []): Results
    {
        return Results::of(
            $this->transport->get('/v5/account/usage', [
                'from' => $this->date($from),
                'to' => $this->date($to),
                'group_by' => $this->enumList($groupBy),
            ])->payload(),
            Usage::from(...),
        );
    }

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
    ): Results {
        return Results::of(
            $this->transport->get('/v5/account/requests', [
                'from' => $this->dateTime($from),
                'to' => $this->dateTime($to),
                'key_id' => $keyId,
                'status' => $status,
                'limit' => $limit,
                'cursor' => $cursor,
            ])->payload(),
            RequestLogEntry::from(...),
        );
    }
}
