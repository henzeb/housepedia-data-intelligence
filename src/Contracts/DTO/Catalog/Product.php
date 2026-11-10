<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface Product
{
    public function id(): string;

    public function name(): LocalizedText;

    public function description(): LocalizedText;

    public function family(): string;

    public function method(): string;

    public function path(): string;

    public function credits(): int;

    public function multiplier(): ?string;

    public function status(): string;

    public function cacheMaxAge(): ?int;

    public function docsUrl(): ?LocalizedText;

    public function contract(): bool;

    public function contact(): ?string;
}
