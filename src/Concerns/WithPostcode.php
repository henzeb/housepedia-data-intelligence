<?php

namespace Henzeb\Housepedia\Data\Intelligence\Concerns;

use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

trait WithPostcode
{
    public function withPostcode(string $postcode, int $houseNumber, ?string $extension = null): static
    {
        return $this->withLocator(Locator::postcode($postcode, $houseNumber, $extension));
    }

    abstract public function withLocator(Locator $locator): static;
}
