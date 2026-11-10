<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Usage
{
    public function date(): ?DateTimeImmutable;

    public function endpoint(): ?string;

    public function keyId(): ?string;

    public function calls(): int;

    public function succeeded(): int;

    public function failed(): int;

    public function credits(): int;

    public function kadasterRecords(): int;
}
