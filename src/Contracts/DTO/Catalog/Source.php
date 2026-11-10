<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface Source
{
    public function id(): string;

    public function name(): string;

    public function cadence(): ?string;

    public function licence(): ?string;

    public function attribution(): ?string;

    public function url(): ?string;
}
