<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface RequestLogEntry
{
    public function requestId(): string;

    public function at(): DateTimeImmutable;

    public function keyId(): ?string;

    public function keyKind(): ?string;

    public function method(): string;

    public function path(): ?string;

    public function status(): int;

    public function credits(): int;

    public function durationInMilliseconds(): ?float;

    public function clientIp(): ?string;
}
