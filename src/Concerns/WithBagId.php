<?php

namespace Henzeb\Housepedia\Data\Intelligence\Concerns;

use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

trait WithBagId
{
    public function withBagId(string $bagId): static
    {
        return $this->withLocator(Locator::bagId($bagId));
    }

    abstract public function withLocator(Locator $locator): static;
}
