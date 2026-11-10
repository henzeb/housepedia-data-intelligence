<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use DateTimeInterface;
use Generator;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\LazyAccount as LazyAccountContract;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\RequestLogEntry;

class LazyAccount implements LazyAccountContract
{
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
    ) {
    }

    /**
     * @return Generator<int, RequestLogEntry>
     */
    public function requests(
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        ?string $keyId = null,
        ?int $status = null,
        ?int $limit = null,
    ): Generator {
        $account = new Account($this->transport);
        $cursor = null;

        do {
            $page = $account->requests($from, $to, $keyId, $status, $limit, $cursor);

            foreach ($page as $entry) {
                yield $entry;
            }

            $cursor = $page->meta()?->nextCursor();
        } while ($cursor !== null);
    }
}
