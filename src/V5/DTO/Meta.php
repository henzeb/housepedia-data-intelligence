<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Meta as MetaContract;
use Henzeb\Housepedia\Data\Intelligence\RateLimit;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Meta implements MetaContract
{
    /**
     * @param list<string>|null $withheld
     */
    public function __construct(
        private ?string $requestId,
        private ?DateTimeImmutable $asOf,
        private ?Credits $credits,
        private ?string $nextCursor,
        private ?array $withheld,
        private ?RateLimit $rateLimit = null,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('request_id'),
            $payload->date('as_of'),
            $payload->nested('credits', Credits::from(...)),
            $payload->string('next_cursor'),
            $payload->nullableStrings('withheld'),
            $payload->rateLimit(),
        );
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function asOf(): ?DateTimeImmutable
    {
        return $this->asOf;
    }

    public function credits(): ?Credits
    {
        return $this->credits;
    }

    public function nextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * @return list<string>|null
     */
    public function withheld(): ?array
    {
        return $this->withheld;
    }

    public function rateLimit(): ?RateLimit
    {
        return $this->rateLimit;
    }
}
