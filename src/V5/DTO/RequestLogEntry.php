<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\RequestLogEntry as RequestLogEntryContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class RequestLogEntry implements RequestLogEntryContract
{
    public function __construct(
        private string $requestId,
        private DateTimeImmutable $at,
        private ?string $keyId,
        private ?string $keyKind,
        private string $method,
        private ?string $path,
        private int $status,
        private int $credits,
        private ?float $durationInMilliseconds,
        private ?string $clientIp,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('request_id'),
            $payload->requireDate('at'),
            $payload->string('key_id'),
            $payload->string('key_kind'),
            $payload->requireString('method'),
            $payload->string('path'),
            $payload->requireInteger('status'),
            $payload->requireInteger('credits'),
            $payload->float('duration_ms'),
            $payload->string('client_ip'),
        );
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function at(): DateTimeImmutable
    {
        return $this->at;
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function keyKind(): ?string
    {
        return $this->keyKind;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function credits(): int
    {
        return $this->credits;
    }

    public function durationInMilliseconds(): ?float
    {
        return $this->durationInMilliseconds;
    }

    public function clientIp(): ?string
    {
        return $this->clientIp;
    }
}
