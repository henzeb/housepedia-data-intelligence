<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface LocalizedText
{
    public function en(): ?string;

    public function nl(): ?string;
}
