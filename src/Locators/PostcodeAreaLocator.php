<?php

namespace Henzeb\Housepedia\Data\Intelligence\Locators;

use Stringable;

class PostcodeAreaLocator implements Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function postcode4(string $postcode): self
    {
        return new self('postcode4:' . rawurlencode(trim($postcode)));
    }

    public static function postcode6(string $postcode): self
    {
        return new self('postcode6:' . rawurlencode(strtoupper(str_replace(' ', '', trim($postcode)))));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
