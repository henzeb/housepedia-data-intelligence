<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Api;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Throwable;

class AddressAmbiguousException extends ConflictException
{
    /**
     * @param list<array<string, mixed>> $errors
     */
    public function __construct(
        int $status,
        string $errorCode,
        string $title,
        ?string $detail = null,
        ?string $requestId = null,
        array $errors = [],
        ?string $type = null,
        ?int $retryAfter = null,
        ?Throwable $previous = null,
        private ?Api $api = null,
    ) {
        parent::__construct($status, $errorCode, $title, $detail, $requestId, $errors, $type, $retryAfter, $previous);
    }

    /**
     * @return list<Address>
     */
    public function candidates(): array
    {
        $api = $this->api ?? (new Config())->api();

        return array_map(
            static fn (array $candidate): Address => $api->address(new Payload($candidate)),
            $this->errors(),
        );
    }
}
